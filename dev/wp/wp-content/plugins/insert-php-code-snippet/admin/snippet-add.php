<?php
if ( ! defined( 'ABSPATH' ) )
    exit;

global $wpdb;
$goback=1;
$_POST = stripslashes_deep($_POST);
$_POST = xyz_trim_deep($_POST);
$xyz_ips_insertionLocation ='';
if(isset($_POST) && isset($_POST['addSubmit'])){
    if (! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce($_REQUEST['_wpnonce'],'ips-padd_')){
        wp_nonce_ays( 'ips-padd_' );
        exit;
    }
    $goback=intval($_POST['goback']);
    $goback++;
    $temp_xyz_ips_title = str_replace(' ', '', $_POST['snippetTitle']);
    $temp_xyz_ips_title = str_replace('-', '', $temp_xyz_ips_title);
    $xyz_ips_title = str_replace(' ', '-', $_POST['snippetTitle']);
    $xyz_ips_insertionMethod = intval($_POST['xyz_ips_insertionMethod']);
	$xyz_ips_insertionMethod = ($xyz_ips_insertionMethod >= XYZ_IPS_INSERTION_METHOD['AUTOMATIC'] && $xyz_ips_insertionMethod <= XYZ_IPS_INSERTION_METHOD['EXECUTE_ON_DEMAND']) ? $xyz_ips_insertionMethod : XYZ_IPS_INSERTION_METHOD['AUTOMATIC'];
    $xyz_ips_insertionLocation = intval($_POST['xyz_ips_insertionLocation']);
    if ($xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['ADMIN_RUN_ON_HEADER'] || $xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['ADMIN_RUN_ON_FOOTER']) {
		$xyz_ips_insertionLocationType = XYZ_IPS_INSERTION_LOCATION_TYPE['ADMIN'];
    }
    else if($xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['FRONTEND_RUN_ON_HEADER'] || $xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['FRONTEND_RUN_ON_FOOTER']){
		$xyz_ips_insertionLocationType = XYZ_IPS_INSERTION_LOCATION_TYPE['FRONT_END'];
    }
    else{

		$xyz_ips_insertionLocationType = 0;
	}
    $xyz_ips_content = $_POST['snippetContent'];

    if($xyz_ips_title != "" && $xyz_ips_content != ""){
        if(ctype_alnum($temp_xyz_ips_title)){
            $snippet_count = $wpdb->query($wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'xyz_ips_short_code WHERE title=%s',$xyz_ips_title) ) ;
            if($snippet_count == 0){

              if(get_option('xyz_ips_auto_insert')==1){
                  $xyz_ips_content_start='<?php';
                  $new_line="\r\n";
                  $xyz_ips_content_end='?>';
    
                  if (stripos($xyz_ips_content, '<?php') !== false)
                      $tag_start_position=stripos($xyz_ips_content,'<?php');
                  else
                      $tag_start_position="-1";
    
                  if (stripos($xyz_ips_content, '?>') !== false)
                      $tag_end_position=stripos($xyz_ips_content,'?>');
                  else
                      $tag_end_position="-1";
    
                if(stripos($xyz_ips_content, '<?php') === false && stripos($xyz_ips_content, '?>') === false){
                  $xyz_ips_content=$xyz_ips_content_start.$new_line.$xyz_ips_content;
                  }
                  else if(stripos($xyz_ips_content, '<?php') !== false){
                      if($tag_start_position>=0 && $tag_end_position>=0 && $tag_start_position>$tag_end_position){
                          $xyz_ips_content=$xyz_ips_content_start.$new_line.$xyz_ips_content;
                      }
                  }
                  else if(stripos($xyz_ips_content, '<?php') === false){
                      if (stripos($xyz_ips_content, '?>') !== false){
                          $xyz_ips_content=$xyz_ips_content_start.$new_line.$xyz_ips_content;
                      }
                  }
              }


                $xyz_shortCode = '[xyz-ips snippet="'.$xyz_ips_title.'"]';
                $wpdb->insert($wpdb->prefix.'xyz_ips_short_code', array('title' =>$xyz_ips_title,'insertionMethod' => $xyz_ips_insertionMethod, 'insertionLocation' => $xyz_ips_insertionLocation, 'insertionLocationType' => $xyz_ips_insertionLocationType,'content'=>$xyz_ips_content,'short_code'=>$xyz_shortCode,'status'=>'1'),array('%s','%d','%d','%d','%s','%s','%d'));
                wp_safe_redirect(admin_url('admin.php?page=insert-php-code-snippet-manage&xyz_ips_msg=1'));
            }
            else{
?>
<div class="xyz_ips_system_notice_area_style0" id="xyz_ips_system_notice_area">
    PHP Snippet already exists. &nbsp;&nbsp;&nbsp;
    <span id="xyz_ips_system_notice_area_dismiss">Dismiss</span>
</div>
<?php
            }
        }
        else{
?>
<div class="xyz_ips_system_notice_area_style0" id="xyz_ips_system_notice_area">
    PHP Snippet title can have only alphabets,numbers or hyphen. &nbsp;&nbsp;&nbsp;
    <span id="xyz_ips_system_notice_area_dismiss">Dismiss</span>
</div>
<?php
        }
    }else{
?>
<div class="xyz_ips_system_notice_area_style0" id="xyz_ips_system_notice_area">
    Fill all mandatory fields. &nbsp;&nbsp;&nbsp;
    <span id="xyz_ips_system_notice_area_dismiss">Dismiss</span>
</div>
<?php
    }
}
?>
<div >
    <fieldset
    style="width: 99%; border: 1px solid #F7F7F7; padding: 10px 0px;">
        <legend>
            <b>
                Add PHP Snippet
            </b>
        </legend>
        <form name="frmmainForm" id="frmmainForm" method="post">
            <?php wp_nonce_field('ips-padd_'); ?>
        
            
            <div>
              	<input type="hidden"  name="goback" value=<?php echo $goback;?>>
                <table
                style="width: 99%; background-color: #F9F9F9; border: 1px solid #E4E4E4; border-width: 1px;margin: 0 auto">
                    <tr>
                        <td>
                            <br/>
                            <div id="shortCode">
                            </div>
                            <br/>
                        </td>
                    </tr>
					<tr valign="top">
						<td style="border-bottom: none;width:20%;">
							&nbsp;&nbsp;&nbsp;Placement Methods &nbsp;
							<font color="red">
								*
							</font>
						</td>
						<td style="border-bottom: none;width:1px;">
                        &nbsp;:&nbsp;
						</td>
						<td>
							<select class="xyz_ips_uniq_select" name="xyz_ips_insertionMethod"
								id="xyz_ips_insertionMethod">
								<option value="<?php echo XYZ_IPS_INSERTION_METHOD['AUTOMATIC']; ?>" <?php if (isset($_POST['xyz_ips_insertionMethod']) && $_POST['xyz_ips_insertionMethod'] == XYZ_IPS_INSERTION_METHOD['AUTOMATIC']) {
									   echo "selected";
								   } ?>>Automatic</option>
								<option value="<?php echo XYZ_IPS_INSERTION_METHOD['SHORT_CODE']; ?>" <?php if (isset($_POST['xyz_ips_insertionMethod']) && $_POST['xyz_ips_insertionMethod'] == XYZ_IPS_INSERTION_METHOD['SHORT_CODE']) {
									   echo "selected";
								   } ?>>Short code (Manual)</option>
								<option value="<?php echo XYZ_IPS_INSERTION_METHOD['EXECUTE_ON_DEMAND']; ?>" <?php if (isset($_POST['xyz_ips_insertionMethod']) && $_POST['xyz_ips_insertionMethod'] == XYZ_IPS_INSERTION_METHOD['EXECUTE_ON_DEMAND']) {
									   echo "selected";
								   } ?>>Execute on demand</option>
							</select>
						</td>
					</tr>


                    <tr valign="top" id="xyz_ips_insertionLocationTR">
                        <td style="border-bottom: none;width:20%;">
                            &nbsp;&nbsp;&nbsp;Insertion Location &nbsp;
                            <font color="red">
                                *
                            </font>
                        </td>
                        <td style="border-bottom: none;width:1px;">
                        &nbsp;:&nbsp;
                        </td>
                        <td>
                            <div>
    <div class="xyz_ips_insertionLocationDiv">
    <span id="xyz_ips_insertion-location-txt">Select Insertion Location </span><span><i class="fa fa-angle-down" aria-hidden="true"></i> <i class="fa fa-angle-up" aria-hidden="true"></i></span>

    </div>
    <input type="hidden" id="xyz_ips_insertionLocation" name="xyz_ips_insertionLocation" value="<?php echo intval($xyz_ips_insertionLocation);?>">
    <ul id="xyz_ips_uniq_list" class="xyz_ips_uniq_list">
    <div class="xyz_ips_left_column">
    
    <li class="xyz_ips_li_h2"><label>Admin</label></li>
    <li class="xyz_ips_li_option <?php echo ($xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['ADMIN_RUN_ON_HEADER']) ? 'selected' : ''; ?>" 
        data-value="<?php echo XYZ_IPS_INSERTION_LOCATION['ADMIN_RUN_ON_HEADER']; ?>">
        Run on header
    </li>
    <li class="xyz_ips_li_option <?php echo ($xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['ADMIN_RUN_ON_FOOTER']) ? 'selected' : ''; ?>" 
        data-value="<?php echo XYZ_IPS_INSERTION_LOCATION['ADMIN_RUN_ON_FOOTER']; ?>">
        Run on footer
    </li>
    <li class="xyz_ips_li_h2"><label>Front end</label></li>
    <li class="xyz_ips_li_option <?php echo ($xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['FRONTEND_RUN_ON_HEADER']) ? 'selected' : ''; ?>" 
        data-value="<?php echo XYZ_IPS_INSERTION_LOCATION['FRONTEND_RUN_ON_HEADER']; ?>">
        Run on header
    </li>
    <li class="xyz_ips_li_option <?php echo ($xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['FRONTEND_RUN_ON_FOOTER']) ? 'selected' : ''; ?>" 
        data-value="<?php echo XYZ_IPS_INSERTION_LOCATION['FRONTEND_RUN_ON_FOOTER']; ?>">
        Run on footer
    </li>
    
    </div>
    <div class="xyz_ips_right_column">
    
    </div>
</ul>
</div>

                        </td>
                    </tr>
                    <tr valign="top">
                        <td style="border-bottom: none;width:20%;">
                            &nbsp;&nbsp;&nbsp;Tracking Name&nbsp;
                            <font color="red">
                                *
                            </font>
                        </td>
                        <td style="border-bottom: none;width:1px;">
                            &nbsp;:&nbsp;
                        </td>
                        <td>
                            <input style="width:80%;"
                            type="text" name="snippetTitle" id="snippetTitle"
                            value="<?php if(isset($_POST['snippetTitle'])){ echo esc_attr($_POST['snippetTitle']);}?>">
                        </td>
                    </tr>
                    <tr>

                        <td style="border-bottom: none;width:20%;">
                                                <?php
                        if(get_option('xyz_ips_auto_insert')==1){
                          ?>
                            &nbsp;&nbsp;&nbsp;PHP code
                        <?php
                          }
                          else{
                        ?>
                          &nbsp;&nbsp;&nbsp;PHP code (without &lt;?php ?&gt;)&nbsp;
                        <?php    
                          }
                        ?>
                            <font color="red">
                                *
                            </font>
                        </td>
                
                        <td style="border-bottom: none;width:1px;">
                            &nbsp;:&nbsp;
                        </td>
                        <td >
                            <textarea name="snippetContent" style="width:80%;height:150px;"><?php if(isset($_POST['snippetContent'])){ echo esc_textarea($_POST['snippetContent']);}?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        </td>
					    <td>
					       
			                
							<input class="button-primary" style="cursor: pointer;"
							type="button" name="back" value="Back" onclick=" window.history.go(-<?php echo $goback;?>);" >
						</td>
                        <td>
                            <input class="button-primary" style="cursor: pointer;"
                            type="submit" name="addSubmit" value="Create">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <br/>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    </fieldset>
</div>
<?php
require( dirname( __FILE__ ) . '/snippet-js.php' );
?>
