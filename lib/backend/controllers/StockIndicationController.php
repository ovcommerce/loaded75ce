<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;

/**
 * default controller to handle user requests.
 */
class StockIndicationController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_SETTINGS_BOX_STOCK_INDICATION', 'BOX_SETTINGS_BOX_STOCK_INDICATION_INDICATION'];

  public function __construct($id, $module)
  {
    parent::__construct($id, $module);
    \common\helpers\Translation::init('admin/stock-indication');
  }


  public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('settings', 'product_stock_indication', FILENAME_STOCK_INDICATION_INDICATION);
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl(FILENAME_STOCK_INDICATION_INDICATION.'/index'), 'title' => HEADING_TITLE);
      
      $this->view->headingTitle = HEADING_TITLE;
      
      $this->view->ViewTable = array(
        array(
            'title' => TABLE_HEADING_STOCK_INDICATION,
            'not_important' => 0,
        ),
        array(
          'title' => TEXT_ALLOW_OUT_OF_STOCK_CHECKOUT,
          'not_important' => 0,
        ),
        array(
          'title' => TEXT_ALLOW_OUT_OF_STOCK_ADD_TO_CART,
          'not_important' => 0,
        ),
      );

        $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);
        return $this->render('index', array('messages' => $messages));
      
    }

    public function actionList() {
        global $languages_id;
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cID = Yii::$app->request->get('cID', 0);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search .= " and (st.stock_indication_text like '%" . $keywords . "%')";
        }

        $formFilter = Yii::$app->request->get('filter','');
        parse_str($formFilter, $filter);

        $current_page_number = ($start / $length) + 1;
        $responseList = array();
        
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "st.stock_indication_text " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "s.sort_order";
                    break;
            }
        } else {
            $orderBy = "s.sort_order";
        }    
        
        $orders_status_query_raw =
          "select s.*, st.stock_indication_text " .
          "from " . TABLE_PRODUCTS_STOCK_INDICATION . " s " .
          " left join " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " st ON st.stock_indication_id=s.stock_indication_id AND st.language_id='".(int)$languages_id . "' " . $search . " ".
          "order by {$orderBy}";

        $orders_status_split = new \splitPageResults($current_page_number, $length, $orders_status_query_raw, $orders_status_query_numrows);
        $orders_status_query = tep_db_query($orders_status_query_raw);
        
        while ($item_data = tep_db_fetch_array($orders_status_query)) {
    
            $responseList[] = array(
              '<div class="handle_cat_list"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr cat_no_folder">' .
                ($item_data['is_default']? '<b>' . $item_data['stock_indication_text'] . ' (' . TEXT_DEFAULT . ')</b>': $item_data['stock_indication_text']) .
                tep_draw_hidden_field('id', $item_data['stock_indication_id'], 'class="cell_identify"').
                '<input class="cell_type" type="hidden" value="top">'.
              '</div></div>',
              '<div>'.($item_data['allow_out_of_stock_checkout']?TEXT_STOCK_INDICATION_YES:'').'</div>',
              '<div>'.($item_data['allow_out_of_stock_add_to_cart']?TEXT_STOCK_INDICATION_YES:'').'</div>',
            );
        }
        
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $orders_status_query_numrows,
            'recordsFiltered' => $orders_status_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);          
        
    }
    
    public function actionListActions() {
      global $language, $languages_id;

      $stock_indication_id = Yii::$app->request->post('stock_indication_id', 0);
      $this->layout = false;

      if (!$stock_indication_id) return;

      $odata = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS_STOCK_INDICATION . " where stock_indication_id='" . (int)$stock_indication_id . "'"));
      $get_text_r = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_STOCK_INDICATION_TEXT." WHERE stock_indication_id='" . (int)$stock_indication_id . "'");
      $odata['text'] = array();
      if ( tep_db_num_rows($get_text_r)>0 ) {
        while ($_text = tep_db_fetch_array($get_text_r)) {
          $odata['text'][ $_text['language_id'] ] = $_text;
        }
      }

      $oInfo = new \objectInfo($odata, false);

      echo '<div class="or_box_head">' . (isset($oInfo->text[$_SESSION['languages_id']])?$oInfo->text[$_SESSION['languages_id']]['stock_indication_text']:'&nbsp;') . '</div>';

      echo '<div class="row_or">' . TEXT_ALLOW_OUT_OF_STOCK_CHECKOUT . ' <b>'.(!!$oInfo->allow_out_of_stock_checkout?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      echo '<div class="row_or">' . TEXT_ALLOW_OUT_OF_STOCK_ADD_TO_CART . ' <b>'.(!!$oInfo->allow_out_of_stock_add_to_cart?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';

      echo '<div class="row_or">' . TEXT_ALLOW_IN_STOCK_NOTIFY . ' <b>'.(!!$oInfo->allow_in_stock_notify?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      echo '<div class="row_or">' . TEXT_REQUEST_FOR_QUOTE . ' <b>'.(!!$oInfo->request_for_quote?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      echo '<div class="row_or">' . TEXT_IS_HIDDEN . ' <b>'.(!!$oInfo->is_hidden?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      echo '<div class="row_or">' . TEXT_DISABLE_PRODUCT_ON_OOS . ' <b>'.(!!$oInfo->disable_product_on_oos?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';

      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo
        '<button class="btn btn-edit btn-no-margin" onclick="itemEdit('.$stock_indication_id.')">' . IMAGE_EDIT . '</button>'.
        '<button class="btn btn-delete" onclick="itemDelete('.$stock_indication_id.')">' . IMAGE_DELETE . '</button>';
      echo '</div>';
    }
    
    public function actionEdit() {

      $stock_indication_id = intval(Yii::$app->request->get('stock_indication_id', 0));

      $odata = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS_STOCK_INDICATION . " where stock_indication_id='" . (int)$stock_indication_id . "'"));
      $get_text_r = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_STOCK_INDICATION_TEXT." WHERE stock_indication_id='" . (int)$stock_indication_id . "'");
      $odata['text'] = array();
      if ( tep_db_num_rows($get_text_r)>0 ) {
        while ($_text = tep_db_fetch_array($get_text_r)) {
          $odata['text'][ $_text['language_id'] ] = $_text;
        }
      }

      $oInfo = new \objectInfo($odata, false);


      $status_inputs_string = '';
      $languages = \common\helpers\Language::get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $status_inputs_string .=
          '<div class="langInput">' . $languages[$i]['image'] . tep_draw_input_field('stock_indication_text[' . $languages[$i]['id'] . ']', $oInfo->text[$languages[$i]['id']]['stock_indication_text']) . '</div>';
      }

      echo tep_draw_form('stock_indication', FILENAME_STOCK_INDICATION_INDICATION. '/save', 'stock_indication_id=' . $oInfo->stock_indication_id);
      if ($stock_indication_id) {
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_STOCK_INDICATION . '</div>';
      } else {
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_STOCK_INDICATION . '</div>';
      }
      echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';

      /*echo '<div class="check_linear"><label><span>' . TEXT_SHOW_STOCK_CODE . '</span> ' . tep_draw_pull_down_menu('stock_code',array(
          array('id'=>'out-stock','text'=> TEXT_PRODUCT_NOT_AVAILABLE),
          array('id'=>'in-stock','text'=> TEXT_PRODUCT_AVAILABLE),
          array('id'=>'transit','text'=> TEXT_INVENTORY_TRANSIT),
          array('id'=>'pre-order','text'=> TEXT_PRE_ORDER),
        ), $oInfo->stock_code) . '</label></div>';
*/
      echo '<div class="check_linear"><label>' . tep_draw_checkbox_field('allow_out_of_stock_checkout',1, !!$oInfo->allow_out_of_stock_checkout) . '<span>' . TEXT_ALLOW_OUT_OF_STOCK_CHECKOUT . '</span></label></div>';

      echo '<div class="check_linear"><label>' . tep_draw_checkbox_field('allow_out_of_stock_add_to_cart',1, !!$oInfo->allow_out_of_stock_add_to_cart) . '<span>' . TEXT_ALLOW_OUT_OF_STOCK_ADD_TO_CART . '</span></label></div>';

      echo '<div class="check_linear"><label>' . tep_draw_checkbox_field('allow_in_stock_notify',1, !!$oInfo->allow_in_stock_notify) . '<span>' . TEXT_ALLOW_IN_STOCK_NOTIFY . '</span></label></div>';
      echo '<div class="check_linear"><label>' . tep_draw_checkbox_field('request_for_quote',1, !!$oInfo->request_for_quote) . '<span>' . TEXT_REQUEST_FOR_QUOTE . '</span></label></div>';
      echo '<div class="check_linear"><label>' . tep_draw_checkbox_field('is_hidden',1, !!$oInfo->is_hidden) . '<span>' . TEXT_IS_HIDDEN . '</span></label></div>';
      echo '<div class="check_linear"><label>' . tep_draw_checkbox_field('disable_product_on_oos',1, !!$oInfo->disable_product_on_oos) . '<span>' . TEXT_DISABLE_PRODUCT_ON_OOS . '</span></label></div>';

      if (!$oInfo->is_default) echo '<div class="check_linear"><br>' . tep_draw_checkbox_field('is_default',1) . '<span>' . TEXT_SET_DEFAULT . '</span></div>';
      echo '<div class="col_desc">' . TEXT_INFO_STOCK_INDICATOR_TEXT . '</div>';
      echo $status_inputs_string;
      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo
        '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="itemSave('.($oInfo->stock_indication_id?$oInfo->stock_indication_id:0).')">'.
        '<input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
      echo '</div>';
      echo '</form>';
    }
    
    public function actionSave() {

      $stock_indication_id = Yii::$app->request->get('stock_indication_id', 0);

      $is_default = intval(Yii::$app->request->post('is_default',0));
      $allow_out_of_stock_add_to_cart = intval(Yii::$app->request->post('allow_out_of_stock_add_to_cart',0));
      $allow_out_of_stock_checkout = intval(Yii::$app->request->post('allow_out_of_stock_checkout',0));

      $allow_in_stock_notify = intval(Yii::$app->request->post('allow_in_stock_notify',0));
      $request_for_quote = intval(Yii::$app->request->post('request_for_quote',0));
      $is_hidden = intval(Yii::$app->request->post('is_hidden',0));
      $disable_product_on_oos = intval(Yii::$app->request->post('disable_product_on_oos',0));

      //$stock_code = Yii::$app->request->post('stock_code','out-stock');


      $stock_indication_text = tep_db_prepare_input(Yii::$app->request->post('stock_indication_text'));

      if ($stock_indication_id == 0) {
        $next_sort_order = tep_db_fetch_array(tep_db_query(
          "SELECT MAX(sort_order) AS sort_order FROM " . TABLE_PRODUCTS_STOCK_INDICATION . " where 1"
        ));
        $next_sort_order = (int)$next_sort_order['sort_order']+1;

        tep_db_perform(TABLE_PRODUCTS_STOCK_INDICATION,array(
          'is_default' => $is_default,
          //'stock_code' => $stock_code,
          'allow_out_of_stock_checkout' => $allow_out_of_stock_checkout,
          'allow_out_of_stock_add_to_cart' => $allow_out_of_stock_add_to_cart,
          'allow_in_stock_notify' => $allow_in_stock_notify,
          'request_for_quote' => $request_for_quote,
          'is_hidden' => $is_hidden,
          'disable_product_on_oos' => $disable_product_on_oos,
          'sort_order' => $next_sort_order,
        ));
        $insert_id = tep_db_insert_id();
      }else{
        $update_data = array(
          //'is_default' => $is_default,
          //'stock_code' => $stock_code,
          'allow_out_of_stock_checkout' => $allow_out_of_stock_checkout,
          'allow_out_of_stock_add_to_cart' => $allow_out_of_stock_add_to_cart,
          'allow_in_stock_notify' => $allow_in_stock_notify,
          'request_for_quote' => $request_for_quote,
          'is_hidden' => $is_hidden,
          'disable_product_on_oos' => $disable_product_on_oos,
        );
        if ( $is_default ) {
          $update_data['is_default'] = $is_default;
        }
        tep_db_perform(TABLE_PRODUCTS_STOCK_INDICATION, $update_data,'update', "stock_indication_id='".(int)$stock_indication_id."'");

        $insert_id = $stock_indication_id;
      }

      $languages = \common\helpers\Language::get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $language_id = $languages[$i]['id'];

        $text_array = array(
          'stock_indication_id' => (int)$insert_id,
          'language_id' => (int)$language_id,
          'stock_indication_text' => isset($stock_indication_text[$language_id])?$stock_indication_text[$language_id]:'',
        );

        $check_text = tep_db_fetch_array(tep_db_query(
          "SELECT COUNT(*) AS c ".
          "FROM ".TABLE_PRODUCTS_STOCK_INDICATION_TEXT." ".
          "WHERE stock_indication_id='".(int)$insert_id."' AND language_id='".(int)$language_id."'"
        ));

        if ( $check_text['c']==0 ) {
          tep_db_perform(TABLE_PRODUCTS_STOCK_INDICATION_TEXT, $text_array);
        }else{
          tep_db_perform(TABLE_PRODUCTS_STOCK_INDICATION_TEXT, $text_array,'update',"stock_indication_id='".(int)$insert_id."' AND language_id='".(int)$language_id."'");
        }
      }

      if ( $is_default && $insert_id!=0 ) {
        tep_db_query("UPDATE ".TABLE_PRODUCTS_STOCK_INDICATION." SET is_default=IF(stock_indication_id='".(int)$insert_id."',1,0)");
      }

      if ($stock_indication_id == 0) {
        $action = 'added';
      }else {
        $action = 'updated';
      }

      echo json_encode(array('message' => 'Stock indication status has been  ' . $action, 'messageType' => 'alert-success'));
    }
    
    
    public function actionDelete() {
      global $language;

      $stock_indication_id =  intval(Yii::$app->request->post('stock_indication_id', 0));

      if ( !$stock_indication_id ) return;

      $get_check_data_r = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_STOCK_INDICATION." WHERE stock_indication_id='{$stock_indication_id}'");
      if ( tep_db_num_rows($get_check_data_r)==0 ) return;
      $check_data = tep_db_fetch_array($get_check_data_r);
      $remove_status = true;
      $error = array();
      if ($check_data['is_default']) {
        $remove_status = false;
        $error = array('message' => ERROR_REMOVE_DEFAULT_STOCK_INDICATION, 'messageType' => 'alert-danger');
      }
      if (!$remove_status) {
        ?>
        <div class="alert fade in <?=$error['messageType']?>">
          <i data-dismiss="alert" class="icon-remove close"></i>
          <span id="message_plce"><?=$error['message']?></span>
        </div>
        <?php
      } else {
        tep_db_query("UPDATE ".TABLE_PRODUCTS." SET stock_indication_id=0 WHERE stock_indication_id='{$stock_indication_id}'");
        tep_db_query("UPDATE ".TABLE_INVENTORY." SET stock_indication_id=0 WHERE stock_indication_id='{$stock_indication_id}'");
        tep_db_query("DELETE FROM " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " where stock_indication_id = '{$stock_indication_id}'");
        tep_db_query("DELETE FROM " . TABLE_PRODUCTS_STOCK_INDICATION . " where stock_indication_id = '{$stock_indication_id}'");
        echo 'reset';
      }
    }

   public function actionSortOrder()
   {
     $moved_id = (int)$_POST['sort_top'];
     $ref_array = (isset($_POST['top']) && is_array($_POST['top']))?array_map('intval',$_POST['top']):array();
     if ( $moved_id && in_array($moved_id, $ref_array) ) {
       // {{ normalize
       $order_counter = 0;
       $order_list_r = tep_db_query(
         "SELECT s.stock_indication_id, s.sort_order ".
         "FROM ". TABLE_PRODUCTS_STOCK_INDICATION ." s ".
         " LEFT JOIN " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " st ON st.stock_indication_id=s.stock_indication_id AND st.language_id='".(int)$_SESSION['languages_id'] . "' " .
         "WHERE 1 ".
         "ORDER BY s.sort_order, st.stock_indication_text"
       );
       while( $order_list = tep_db_fetch_array($order_list_r) ){
         $order_counter++;
         tep_db_query("UPDATE ".TABLE_PRODUCTS_STOCK_INDICATION." SET sort_order='{$order_counter}' WHERE stock_indication_id='{$order_list['stock_indication_id']}' ");
       }
       // }} normalize
       $get_current_order_r = tep_db_query(
         "SELECT stock_indication_id, sort_order ".
         "FROM ".TABLE_PRODUCTS_STOCK_INDICATION." ".
         "WHERE stock_indication_id IN('".implode("','",$ref_array)."') ".
         "ORDER BY sort_order"
       );
       $ref_ids = array();
       $ref_so = array();
       while($_current_order = tep_db_fetch_array($get_current_order_r)){
         $ref_ids[] = (int)$_current_order['stock_indication_id'];
         $ref_so[] = (int)$_current_order['sort_order'];
       }

       foreach( $ref_array as $_idx=>$id ) {
         tep_db_query("UPDATE ".TABLE_PRODUCTS_STOCK_INDICATION." SET sort_order='{$ref_so[$_idx]}' WHERE stock_indication_id='{$id}' ");
       }

     }
   }
}
