<?php
defined('_JEXEC') or die('Restricted access'); 
$products_per_row = $viewData['products_per_row']; 
$app = JFactory::getApplication();
$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
$ini_array = parse_ini_file($template_path);
$addtocart = $ini_array['addtocartbuttonindex'];
$products_per_row = $viewData['products_per_row'];
if( $products_per_row > 0 ) 
{ 
$lg = $products_per_row; 
$sm = $products_per_row; 
$xs = 1; 
}else 
{ 
$lg = 4;
$sm = 4;
$xs = 1;
} 
$class_suffix_lg  = round((12 / $lg)); 
$class_suffix_sm  = round((12 / $sm)); 
$class_suffix_xs  = round((12 / $xs)); 
$currency = $viewData['currency']; 
$showRating = $viewData['showRating']; 
$verticalseparator = "vertical-separator"; 
echo shopFunctionsF::renderVmSubLayout('askrecomjs'); 
$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&Itemid='.$Itemid;
}
foreach ($viewData['products'] as $type => $products ) { 
	$rowsHeight = shopFunctionsF::calculateProductRowsHeights($products,$currency,$products_per_row);
	if(!empty($type) and count($products)>0){
		$productTitle = vmText::_('COM_VIRTUEMART_'.strtoupper($type).'_PRODUCT'); ?>
		<div class="<?php echo $type ?>-view">
  		<h4><?php echo $productTitle ?></h4>
	<?php 
    } 
	$cellwidth = ' width'.floor ( 100 / $products_per_row );
	$BrowseTotalProducts = count($products);
	$col = 1;
	$nb = 1;
	$row = 1;
 	$columncounter = 0; 
	foreach ( $products as $product ) { 
	if ($col == 1 && $nb > $products_per_row) { ?>	
	<div class="horizontal-separator"></div>
		<?php } 
			if ($col == 1) { ?> 
		<div class="row"> 
			<?php } 
			if ($nb == $products_per_row or $nb % $products_per_row == 0) { 
				$show_vertical_separator = ' '; 
			} else { 
				$show_vertical_separator = $verticalseparator; 
			} 
	 ?> 		
	<div class="col-lg-<?php echo $class_suffix_lg;?> col-md-<?php echo $class_suffix_lg;?> col-sm-<?php echo $class_suffix_sm;?> col-xs-<?php echo $class_suffix_xs;?>">
		<article class="ttr_post">
			<div class="ttr_post_content_inner"> 
			<div class="ttr_article grid"> 
			
		<a title="<?php echo $product->product_name ?>" href="<?php echo $product->link.$ItemidStr; ?>" class="product-image">
		<?php echo $product->images[0]->displayMediaThumb('', false); ?>
		</a>  
		<div class="product-shop">
			<div class="product-shop-margin postcontent">
				<div class="ttr_post_inner_box">
					<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>				
					<h2 class="ttr_post_title">
						<?php echo JHtml::link ($product->link.$ItemidStr, $product->product_name); ?>
					</h2> 
					<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>
				</div> 
		<?php echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$product,'currency'=>$currency)); ?>
		<?php echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product,'rowHeights'=>$rowsHeight[$row], 'position' => array('ontop', 'addtocart'))); ?>
		</div>
		</div>	
	</div>	
	</div>			
	</article>
	</div> 	
	<?php  
	$columncounter++;
	$nb ++;
	if( $lg != 0 || $sm != 0 || $xs != 0) 
	{ 
	if(($columncounter) % $lg == 0)  
	{?> 
	<div style="clear: both;" class="visible-xs-block"></div> 
	<div style="clear: both;" class="visible-md-block"></div> 
	<?php 
	}	 
	elseif(($columncounter) % $sm == 0)  
 	{?> 
		<div style="clear: both;" class="visible-sm-block"></div> 
	<?php 
	} 
	elseif(($columncounter) % $xs == 0) 
 	{?> 
		<div style="clear: both;" class="visible-xs-block"></div> 
	<?php 
	} 
	} 
    if ($col == $products_per_row || $nb > $BrowseTotalProducts) { ?> 
     <div class="clear"></div> 
  </div> 
    <?php 
    $col = 1; 
    $row++; 
    } else { 
    $col ++; 
     } 
   } 
 if(!empty($type)and count($products)>0){ 
    ?> 
   <div class="clear"></div> 
   </div> 
    <?php 
     } 
   } 
 ?> 
