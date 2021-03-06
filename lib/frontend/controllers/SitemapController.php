<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\controllers;

use Yii;
use common\classes\Images;

class SitemapController extends Sceleton
{

  public function actionIndex()
  {
      $osC_CategoryTree = new \common\classes\osC_CategoryTree;
      $description = $osC_CategoryTree->buildTree();
      return $this->render('index.tpl', ['description' => $description]);
  }

}