<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * SiteInfo 插件
 *
 * @package SiteInfo
 * @version 1.0
 * @author chatgpt
 * @link https://github.com/dylanbai8
 */

class SiteInfo_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        $footerFile = __TYPECHO_ROOT_DIR__ . '/usr/themes/' . Helper::options()->theme . '/footer.php';

        // 修改footer.php
        if (file_exists($footerFile)) {
            $footerContent = file_get_contents($footerFile);
            if (strpos($footerContent, 'SiteInfo_Plugin::footerOutput') === false) {
                $footerContent = str_replace('</footer>', "<?php SiteInfo_Plugin::footerOutput(\$this); ?>\n</footer>", $footerContent);
                file_put_contents($footerFile, $footerContent);
            }
        }
    }

    public static function deactivate()
    {
        $footerFile = __TYPECHO_ROOT_DIR__ . '/usr/themes/' . Helper::options()->theme . '/footer.php';

        // 还原footer.php
        if (file_exists($footerFile)) {
            $footerContent = file_get_contents($footerFile);
            $footerContent = str_replace("<?php SiteInfo_Plugin::footerOutput(\$this); ?>\n", '', $footerContent);
            file_put_contents($footerFile, $footerContent);
        }
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $displayLocation = new Typecho_Widget_Helper_Form_Element_Radio('displayLocation', 
            array('footer' => '在网站底部显示'), 'footer', '显示位置');
        $form->addInput($displayLocation);

        $startDate = new Typecho_Widget_Helper_Form_Element_Text('startDate', NULL, '2023-01-01', _t('网站创建日期'), _t('请以 YYYY-MM-DD 格式输入网站创建日期'));
        $form->addInput($startDate);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    public static function footerOutput($widget)
    {
        $options = Helper::options();
        if ($options->plugin('SiteInfo')->displayLocation == 'footer') {
            echo self::getSiteInfo();
        }
    }

    public static function getSiteInfo()
    {
        $options = Helper::options();
        $startDate = strtotime($options->plugin('SiteInfo')->startDate);
        $now = time();
        $days = floor(($now - $startDate) / (60 * 60 * 24));

        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $totalPosts = $db->fetchObject($db->select(array('COUNT(*)' => 'num'))->from($prefix.'contents')->where('type = ?', 'post'))->num;

        return "<br>网站文章总数: $totalPosts 篇 | 网站运行时间: $days 天";
    }
}
