<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $baseUrl = '@web/themes/basic/css';
    public $css = [
        'edit-blocks.css',
    ];
}