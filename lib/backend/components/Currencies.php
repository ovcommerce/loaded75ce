<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\components;

use yii\base\Widget;

class Currencies extends Widget {

    public $js_mask_type = 'accounting';// accounting or maskMoney
    public $currency;
    
    public function init()
    {
        global $currency;
        parent::init();
        if (is_null($this->currency)) $this->currency = $currency;
        if (is_null($this->currency)) $this->currency = DEFAULT_CURRENCY;
    }
    
    public function run() {
        global $currencies_id, $currency;
        $response = '';		
        $currency_query = tep_db_query("select * from " . TABLE_CURRENCIES . " where status = 1");
        $currencies_id = tep_db_fetch_array(tep_db_query("select * from " . TABLE_CURRENCIES . " where code = '" . $this->currency . "'"));
        while ($currency = tep_db_fetch_array($currency_query)) {
            
            $response .= 'curr_hex['.$currency['currencies_id'].'] = '.json_encode(array(
                'symbol_left' => $currency['symbol_left'],
                'symbol_right' => $currency['symbol_right'],
                'decimal_point' => $currency['decimal_point'],
                'thousands_point' => $currency['thousands_point'],
                'decimal_places' => (int)$currency['decimal_places'],
              )).';
            ';
            
        
        }
       // print_r($currency);
        return $this->render('Maskmoney', [
          'response' => $response,
          'currencies_id' => $currencies_id,
          'js_mask_type' => $js_mask_type,
        ]);
    }

}