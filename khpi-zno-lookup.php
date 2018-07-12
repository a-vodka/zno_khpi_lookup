<?php

/*
Plugin Name: KhPI ZNO Lookup
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 1.0
Author: Oleksii Vodka
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

define('KHPI_ZNO_LOOKUP_ADMIN_SUFFIX', 'znolookup');

add_shortcode('khpi_zno_lookup', 'khpi_zno_lookup_shortcode');

add_action('admin_menu', 'khpi_zno_lookup_menu');

register_activation_hook(__FILE__, 'khpi_zno_lookup_install');


define('KHPI_ZNO_LOOKUP_TABLENAME', $wpdb->prefix . "khpi_zno_lookup");

global $khpi_zno_lookup_db_version;
$khpi_zno_lookup_db_version = "1.0";

function khpi_zno_lookup_install()
{
    global $wpdb;
    global $khpi_zno_lookup_db_version;

    $table_name = $wpdb->prefix . "khpi_zno_lookup";
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

        $sql = "
        CREATE TABLE `{$table_name}` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `Code` int(3) DEFAULT NULL,
          `Speciality` varchar(250) DEFAULT NULL,
          `Specialization` varchar(250) DEFAULT NULL,
          `faculty` varchar(250) DEFAULT NULL,
          `UkrainianLanguage` double(5,2) NOT NULL DEFAULT '0',
          `Math` double(5,2) NOT NULL DEFAULT '0',
          `Physics` double(5,2) NOT NULL DEFAULT '0',
          `HistoryOfUkraine` double(5,2) NOT NULL DEFAULT '0',
          `ForeignLanguage` double(5,2) NOT NULL DEFAULT '0',
          `Geography` double(5,2) NOT NULL DEFAULT '0',
          `Chemistry` double(5,2) NOT NULL DEFAULT '0',
          `Biology` double(5,2) NOT NULL DEFAULT '0',
          `Certificate` double(5,2) NOT NULL DEFAULT '0',
          `PK` double(5,2) NOT NULL DEFAULT '0',
          `TK` double(5,2) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
        ) ;
     ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option("khpi_zno_lookup_db_version", $khpi_zno_lookup_db_version);

    }
}


function khpi_zno_lookup_menu()
{
    add_menu_page('KhPI ZNO Lookup options', 'KhPI ZNO Lookup', 'manage_options', KHPI_ZNO_LOOKUP_ADMIN_SUFFIX, 'khpi_zno_lookup_init', 'dashicons-media-code');
}

function khpi_zno_lookup_init()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }


    khpi_zno_lookup_load_content();


}

function khpi_zno_lookup_showlist()
{
    global $wpdb;
    $zno_objs = $wpdb->get_results("SELECT *  FROM " . KHPI_ZNO_LOOKUP_TABLENAME, OBJECT);
    $addlink = admin_url('admin.php?page=' . KHPI_ZNO_LOOKUP_ADMIN_SUFFIX.'&addnew=1');
    echo "
        <h1>KhPI ZNO Lookup Plugin</h1>
        <h2>Спеціальності/Спеціалізації та конкурсні предмети для вступу  <a href='{$addlink}'' class='button'>Створити нову</a></h2>
        <form  method=\"post\" enctype=\"multipart/form-data\">
                <input type='file' id='upload_csv' name='upload_csv'/>
                <input type='submit' value='Завантажити'>
        </form>
        <table id='zno_table'> 
        <tr>
        <th>Код</th>
        <th>Спеціальінсть</th>
        <th>Спеціалізація</th>
        <th>Факультет/Інститут</th>
        <th>Укр</th>
        <th>Мат</th>
        <th>Фіз</th>
        <th>Іст</th>
        <th>Іноз</th>
        <th>Гео</th>
        <th>Хім</th>
        <th>Біо</th>
        <th>Ат</th>
        <th>ПК</th>
        <th>Т Конк</th>
        <th>X</th>
        </tr>";
    $i = 0;

    foreach ($zno_objs as $obj) {
        $bg = $i % 2 == 0 ? " bgcolor='#cccccc'" : "";
        echo "<tr $bg>";
        {
            $link = $_SERVER['REQUEST_URI'] . "&id=" . $obj->id;
            $dellink = $_SERVER['REQUEST_URI'] . "&delid=" . $obj->id;

            echo "<td><a href='$link'>{$obj->Code}</a></td>";
            echo "<td><a href='$link'>{$obj->Speciality}</a></td>";
            echo "<td><a href='$link'>{$obj->Specialization}</a></td>";
            echo "<td><a href='$link'>{$obj->faculty}</a></td>";
            echo "<td>{$obj->UkrainianLanguage}</td>";
            echo "<td>{$obj->Math}</td>";
            echo "<td>{$obj->Physics}</td>";
            echo "<td>{$obj->HistoryOfUkraine}</td>";
            echo "<td>{$obj->ForeignLanguage}</td>";
            echo "<td>{$obj->Geography}</td>";
            echo "<td>{$obj->Chemistry}</td>";
            echo "<td>{$obj->Biology}</td>";
            echo "<td>{$obj->Certificate}</td>";
            echo "<td>{$obj->PK}</td>";
            echo "<td>{$obj->TK}</td>";
            echo "<td><a href='{$dellink}' title='Видалити' onclick=\"return confirm('Видалити?');\">X</a></td>";
        }
        echo "</tr>";
        $i++;
    }
    echo "</table>";
    echo "
    <script type='application/javascript'>
    
    jQuery('#zno_table tr td').each(function(i,elem){
        if (jQuery(elem).text()=='0.00') 
            jQuery(elem).text('');
        else if (parseFloat(jQuery(elem).text())<0)
        jQuery(elem).css('background','#CCCCFF');
        //console.log(jQuery(elem).text())
    
    });
    
    </script>
    
    ";
}

function khpi_zno_lookup_show_edit_form()
{
    global $wpdb;
    $id = (int)$_REQUEST['id'];
    $zno_objs = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . KHPI_ZNO_LOOKUP_TABLENAME . " WHERE id='%d'", $id), OBJECT);
    $zno_obj = $zno_objs[0];
    $admin_url = admin_url("admin.php?page=" . KHPI_ZNO_LOOKUP_ADMIN_SUFFIX);

    echo "
    <h1>Редагувати запис:</h1>
    <em>Від’ємне значення коефіцієнту вказує, що цей предмет є обов’язковим</em> 
    <form action='{$admin_url}' method='post' id='khpi_zno_lookup_form'>
        <input type='hidden' value='{$zno_obj->id}' name='edit_id'>
        <label>Код спеціальності:<br/><input type='number' value='{$zno_obj->Code}' name='Code'></label><br/>
        <label>Спеціальність:<br/><input type='text' value='{$zno_obj->Speciality}' name='Speciality'></label><br/>
        <label>Спеціалізація:<br/><input type='text' value='{$zno_obj->Specialization}' name='Specialization'></label><br/>
        <label>Факультет/Інститут<br/><input type='text' value='{$zno_obj->faculty}' name='faculty'></label><br/>
        <label>Українська мова та література:<br/><input type='text' value='{$zno_obj->UkrainianLanguage}' name='UkrainianLanguage'></label><br/>
        <label>Математика:<br/><input type='text' value='{$zno_obj->Math}' name='Math'></label><br/>
        <label>Фізика:<br/><input type='text' value='{$zno_obj->Physics}' name='Physics'></label><br/>
        <label>Історія України:<br/><input type='text' value='{$zno_obj->HistoryOfUkraine}' name='HistoryOfUkraine'></label><br/>
        <label>Іноземна мова:<br/><input type='text' value='{$zno_obj->ForeignLanguage}' name='ForeignLanguage'></label><br/>
        <label>Географія:<br/><input type='text' value='{$zno_obj->Geography}' name='Geography'></label><br/>
        <label>Хімія:<br/><input type='text' value='{$zno_obj->Chemistry}' name='Chemistry'></label><br/>
        <label>Біологія:<br/><input type='text' value='{$zno_obj->Biology}' name='Biology'></label><br/>
        <label>Середній бал атестату:<br/><input type='text' value='{$zno_obj->Certificate}' name='Certificate'></label><br/>
        <label>Підкурси:<br/><input type='text' value='{$zno_obj->PK}' name='PK'></label><br/>
        <label>Творчий конкурс:<br/><input type='text' value='{$zno_obj->TK}' name='TK'></label><br/>
        <input type='submit' value='Застосувати'>
    </form>
    
    <script type=\"application/javascript\">
        
    f = function(elem)
        {   
              
         if (parseFloat( elem.value) < 0)
            jQuery(elem).css('background','#CCCCFF');
         else
         if (parseFloat( elem.value) == 0)
         {
            jQuery(elem).css('color','#AAAAAA');
            jQuery(elem).parent().css('color','#AAAAAA');
         }
         else
         if (Math.abs(parseFloat( elem.value)) > 1.0)
            jQuery(elem).css('background','#FFAAAA');
         else
          {
                  jQuery(elem).css('background','white');
                  jQuery(elem).css('color','black');
                  jQuery(elem).parent().css('color','black');
          }
         }         
    
    jQuery('#khpi_zno_lookup_form label input[type!=number]').change(function(){f(this);});
    jQuery('#khpi_zno_lookup_form label input[type!=number]').each(function(i,elem){f(elem);});
    
    
    </script>
    ";

}

function khpi_zno_lookup_load_content()
{
    global $wpdb;
    if (isset($_REQUEST['id'])) {
        khpi_zno_lookup_show_edit_form();
    } elseif (isset($_REQUEST['edit_id'])) {
        $id = (int)$_REQUEST['edit_id'];
        unset($_POST['edit_id']);
        $wpdb->update(KHPI_ZNO_LOOKUP_TABLENAME, $_POST, array('id' => $id));
        wp_redirect(admin_url('admin.php?page=' . KHPI_ZNO_LOOKUP_ADMIN_SUFFIX));

    } elseif (isset($_REQUEST['delid'])) {
        $wpdb->delete( KHPI_ZNO_LOOKUP_TABLENAME, array( 'ID' => (int)$_REQUEST['delid'] ), array( '%d' ));
        wp_redirect(admin_url('admin.php?page=' . KHPI_ZNO_LOOKUP_ADMIN_SUFFIX));
        wp_die();
    } elseif (isset($_REQUEST['addnew'])) {
        $wpdb->insert(KHPI_ZNO_LOOKUP_TABLENAME,
            array(
                'Speciality' => 'Нова спеціальність',
                'Specialization' => 'Нова спеціалізація',
                'Code' => 0
            ),
            array(
                '%s',
                '%s',
                '%d'
            )
        );

        wp_redirect(admin_url('admin.php?page=' . KHPI_ZNO_LOOKUP_ADMIN_SUFFIX . "&id=" . $wpdb->insert_id));
        wp_die();
    } elseif (isset($_FILES['upload_csv'])) {
        if ($_FILES['upload_csv']['error'] != 0 )
            wp_die("Cann't upload file");
        $tmp = $_FILES['upload_csv']['tmp_name'];
        $parts = pathinfo($tmp);
        $fname = $parts['dirname'].DIRECTORY_SEPARATOR.uniqid();
        $fname = str_replace("\\","\\\\" , $fname);
        move_uploaded_file($tmp, $fname);
        
        $q = "
            LOAD DATA INFILE '$fname' REPLACE INTO TABLE ".KHPI_ZNO_LOOKUP_TABLENAME."
            CHARACTER SET cp1251
            FIELDS TERMINATED BY ';' ENCLOSED BY '\"'
            LINES TERMINATED BY '\r\n'
            IGNORE 1 LINES;
        ";

        $wpdb->query($q);
        $wpdb->show_errors(true);

        unlink($fname);
        wp_redirect(admin_url('admin.php?page=' . KHPI_ZNO_LOOKUP_ADMIN_SUFFIX ));
    }

    else {
        khpi_zno_lookup_showlist();
    }

}

function khpi_zno_lookup_shortcode($atts)
{


    $content = <<<HTML
    
<div id="zno" class="zno_row">
    <div id='chkbox' class="zno_col-1-4">
        <p>Оберіть сертифікати ЗНО:</p>
    </div>
    <div id="spec" class="zno_col-3-4"></div>
</div>

HTML;


    return $content;
}


add_action('admin_enqueue_scripts', 'load_admin_style');
add_action('wp_enqueue_scripts', 'load_admin_style');
function load_admin_style()
{
    wp_register_style('admin_zno_css', plugins_url('admin-style.css', __FILE__), false, '1.0.0');
    wp_enqueue_style('admin_zno_css');
}


wp_enqueue_script('my-ajax-request', plugins_url('khpi_zno_lookup_script.js', __FILE__), null, 1, true);
wp_localize_script('my-ajax-request', 'MyAjax', array('ajaxurl' => admin_url('admin-ajax.php')));


add_action('wp_ajax_my_action', 'my_action_callback');
add_action('wp_ajax_nopriv_my_action', 'my_action_callback');
function my_action_callback()
{
    global $wpdb;
    $zno_objs = $wpdb->get_results("SELECT *  FROM " . KHPI_ZNO_LOOKUP_TABLENAME, OBJECT);
    echo json_encode($zno_objs);
    // выход нужен для того, чтобы в ответе не было ничего лишнего, только то что возвращает функция
    wp_die();
}