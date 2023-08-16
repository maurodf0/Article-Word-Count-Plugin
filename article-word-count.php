<?php
/* 
Plugin Name: Article Word Count
Description: Every word count.
Version: 0.1
Author: Mauro De Falco
Author URI: maurodefalco.it
Text Domain: awcm
Domain Path: /languages
*/

class ArticleWordCount {
        function __construct(){
            add_action('admin_menu', array($this, 'adminPage'));
            add_action('admin_init', array($this, 'settings'));
            add_filter('the_content', array($this, 'ifWrap'));
            add_action('init', array($this, 'languages'));
        }

        function languages(){
            load_plugin_textdomain('awcm', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        function ifWrap($content){
            if (
                is_main_query() AND is_single() AND 
            ( 
                get_option('awc_wordcount', 'on') OR 
                get_option('awc_charactercount', 'on') OR 
                get_option('awc_timeread', 'on')
            )){
                return $this->createHTML($content);
            }
            return $content;
        }

        function createHTML($content){
           $html = '<h3>' . esc_html(get_option('awc_headline', 'Post Stats')) .'</h3><p>';

           // calcoliamo il numero di parole
           if(get_option('awc_wordcount', '1') OR get_option('awc_readtime', '1')){
           $wordCount = str_word_count(strip_tags($content));
           }

           if(get_option('awc_wordcount', '1')){
            $html .= __('This post has', 'awcm') . ' ' . $wordCount . ' ' . __('words', 'awcm') .  ' <br>';
           }

           if(get_option('awc_charactercount', '1')){
            $html .= __('This post has', 'awcm') . ' ' . strlen(strip_tags($content)) .  ' characters<br>';
           }

           if(get_option('awc_timeread', '1')){
            $html .= 'This post takes about ' . round($wordCount/225) .  ' minute(s) to read<br>';
           }

           $html .= '</p>';

           if(get_option('awc_location', '0') == '0'){
            return $html . $content ;
           } 
           return $content . $html;
        }

        function settings(){
            add_settings_section('awc_first_section', null, null, 'article-word-count-setting');
            // registriamo i campi delle impostazioni -> location
            add_settings_field('awc_location', 'Display Location', array($this, 'locationHTML'), 'article-word-count-setting', 'awc_first_section');
            register_setting('articlewordcount', 'awc_location', array('sanitize_callback' => array($this, 'customSanitizeLocation'), 'default' => '0'));
            // registriamo i campi delle impostazioni -> Headline
            add_settings_field('awc_headline', 'Headline Text', array($this, 'headlineHTML'), 'article-word-count-setting', 'awc_first_section');
            register_setting('articlewordcount', 'awc_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Stats'));
              // registriamo i campi delle impostazioni -> Word Count Check
              add_settings_field('awc_wordcount', 'Activate Word Count', array($this, 'checkboxHTML'), 'article-word-count-setting', 'awc_first_section', array('theName' => 'awc_wordcount'));
              register_setting('articlewordcount', 'awc_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
              // registriamo i campi delle impostazioni -> Character Count Check
              add_settings_field('awc_charactercount', 'Activate Character Count', array($this, 'checkboxHTML'), 'article-word-count-setting', 'awc_first_section', array('theName' => 'awc_charactercount'));
              register_setting('articlewordcount', 'awc_charactercount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
              // registriamo i campi delle impostazioni -> Time Read
              add_settings_field('awc_timeread', 'Activate Time Read', array($this, 'checkboxHTML'), 'article-word-count-setting', 'awc_first_section', array('theName' => 'awc_timeread'));
              register_setting('articlewordcount', 'awc_timeread', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
        }

        function customSanitizeLocation($value){
            if($value != '0' AND $value != '1'){
                add_settings_error('awc_location', 'awc_location_error', 'Display Location must be begginning or end');
                return get_option('awc_location');
            }
            return $value;
        }

        /* Qui creiamo le singole funzioni per l'html
        function timereadHTML(){?>
            <input type="checkbox" name="awc_timeread" value="0" <?php checked(get_option('awc_timeread'), '0'); ?>>
        <?php }

        function ccountHTML(){?>
            <input type="checkbox" name="awc_charactercount" value="0" <?php checked(get_option('awc_charactercount'), '0');?>>
        <?php }

        function wordcountHTML(){?>
            <input type="checkbox" name="awc_wordcount" value="1" <?php checked(get_option('awc_wordcount'), '1')?>>
        <?php }

         */

        function headlineHTML(){?>
            <input type="text" name="awc_headline" value="<?php echo esc_attr(get_option('awc_headline'));?>">
        <?php }

        // Qui invece ne creimao una sola con un parametro $args che ne gesctisce il name e il checked

        function checkboxHTML($args){?>
            <input type="checkbox" name="<?php echo $args['theName'] ?>" <?php checked(get_option($args['theName']), 'on'); ?>>
        <?php }


         function locationHTML(){ ?>
            <select name="awc_location">
                <option value="0" <?php selected(get_option('awc_location'), '0'); ?>>Beginning of post</option>
                <option value="1" <?php selected(get_option('awc_location'), '1'); ?>>End of Post</option>
            </select>
        <?php } 

        function adminPage(){
            add_options_page('Article Word Count Settings', __('Article Word Count', 'awcm') , 'manage_options', 'article-word-count-setting', array($this, 'HTMLPage'));
        }
        
        function HTMLPage(){ ?>
                <div class="wrap">
                    <h1>Article Word Count Settings</h1>
                    <form action="options.php" method="POST">
                    <?php
                        settings_fields('articlewordcount'); 
                        do_settings_sections('article-word-count-setting'); 
                        submit_button();
                    ?>
                    </form>
                </div>
        <?php }

}

$articleWordCount = new ArticleWordCount();



