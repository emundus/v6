<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
defined('_JEXEC') or die('Restricted access');
?>
<?php

class plgSystemHikashopproducttag extends JPlugin {
	function onHikashopBeforeDisplayView(&$view){
		$option = hikaInput::get()->getString('option');
		$ctrl = hikaInput::get()->getString('ctrl');
		$task = hikaInput::get()->getString('task');

		if ($option!='com_hikashop'||$ctrl!='product'||$task!='show') return;

		ob_start();
	}
	function onHikashopAfterDisplayView(&$view){
		$option = hikaInput::get()->getString('option');
		$ctrl = hikaInput::get()->getString('ctrl');
		$task = hikaInput::get()->getString('task');

		if ($option!='com_hikashop'||$ctrl!='product'||$task!='show') return;

		$config =& hikashop_config();
		$default_params = $config->get('default_params');

		$product_page = ob_get_clean();

		$product_page_parts = explode('class="hikashop_product_page ', $product_page);
		if(!empty($product_page_parts[1])){

			if(!preg_match('#https://schema.org/Product#',$product_page_parts[1])){
				$product_page_parts[1] = 'itemscope itemtype="https://schema.org/Product" class="hikashop_product_page ' .$product_page_parts[1];
			}

			if(!preg_match('#itemprop="name"#',$product_page_parts[1])){
				$pattern='/id="hikashop_product_name_main"/';
				$replacement='id="hikashop_product_name_main" itemprop="name"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);

				$pattern='/id="hikashop_product_code_main"/';
				$replacement='id="hikashop_product_code_main" itemprop="sku"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);

				if($default_params['show_price'] == 1){
					$currency_id = hikashop_getCurrency();
					$null = null;
					$currencyClass = hikashop_get('class.currency');
					$currencies = $currencyClass->getCurrencies($currency_id,$null);
					$data=$currencies[$currency_id];

					$pattern='/<(span|div) id="hikashop_product_price_main" class="hikashop_product_price_main">/';
					$replacement= '<div itemprop="offers" itemscope itemtype="https://schema.org/Offer"><$1 id="hikashop_product_price_main" class="hikashop_product_price_main"><meta itemprop="priceCurrency" content="'.$data->currency_code.'" />';
					$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);

					$pattern='/class="hikashop_product_price_main"(.*)class="hikashop_product_price hikashop_product_price_0(.*)>(.*)<\/span>/msU';
					preg_match($pattern, $product_page_parts[1] , $matches);
					if(isset($matches[3])){
						$mainPrice = str_replace(array(' ',$data->currency_symbol),'',preg_replace('/\((.*)\)/','',$matches[3]));

						$replacement = 'class="hikashop_product_price_main" $1 class="hikashop_product_price hikashop_product_price_0$2><span itemprop="price" style="display: none;">'.$mainPrice.'</span>$3</span>';
						$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);
					}
				}

				$pattern='/class="hikashop_product_description_main"/';
				$replacement='class="hikashop_product_description_main" itemprop="description"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);
			}

			if(!preg_match('#https://schema.org/Review#',$product_page_parts[1])){
				$pattern='/id="hikashop_product_vote_listing"/';
				$replacement='id="hikashop_product_vote_listing" itemscope itemtype="https://schema.org/Review"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);
			}

			if($default_params['show_price'] == 1 && !preg_match('#itemprop="eligibleQuantity"#',$product_page_parts[1])){
				$pattern='/class="hikashop_product_price_per_unit"/';
				$replacement='class="hikashop_product_price_per_unit" itemprop="eligibleQuantity"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);
			}

			if(!preg_match('#itemprop="image"#',$product_page_parts[1])){
				$pattern='/id="hikashop_main_image"/';
				$replacement='id="hikashop_main_image" itemprop="image"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1]);
			}

			if(!preg_match('#itemprop="width"#',$product_page_parts[1])){
				$pattern='/class="hikashop_product_width_main"/';
				$replacement='class="hikashop_product_width_main" itemprop="width"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);

				$pattern='/class="hikashop_product_height_main"/';
				$replacement='class="hikashop_product_height_main" itemprop="height"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);

				$pattern='/class="hikashop_product_length_main"/';
				$replacement='class="hikashop_product_length_main" itemprop="depth"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);

				$pattern='/class="hikashop_product_weight_main"/';
				$replacement='class="hikashop_product_weight_main" itemprop="weight"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);
			}

			if($default_params['show_price'] == 1){
				$pattern='/<(span|div) id="(hikashop_product_weight_main|hikashop_product_width_main|hikashop_product_length_main|hikashop_product_height_main|hikashop_product_characteristics|hikashop_product_options|hikashop_product_custom_item_info|hikashop_product_price_with_options_main|hikashop_product_quantity_main)"/';
				$replacement='</div> <$1 id="$2"';
			}

			if(!preg_match('#itemtype="https://schema.org/Review"#',$product_page_parts[1])){
				if(strpos($product_page_parts[1],'class="hika_comment_listing_empty"')==false){
					$pattern='/class="ui-corner-all hika_comment_listing"/';
					$replacement='class="ui-corner-all hika_comment_listing" itemprop="review" itemscope itemtype="https://schema.org/Review"';
					$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1]);
				}

				$pattern='/class="hika_comment_listing_content"/';
				$replacement='class="hika_comment_listing_content" itemprop="description"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1]);

				$pattern='/class="hika_comment_listing_name"/';
				$replacement='class="hika_comment_listing_name" itemprop="author" itemscope itemtype="https://schema.org/Person"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1]);

				$pattern='/class="hika_vote_listing_username"/';
				$replacement='class="hika_vote_listing_username" itemprop="author"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1]);
			}

			if(!preg_match('#itemprop="aggregateRating"#',$product_page_parts[1])){
				$pattern='/class="hikashop_vote_stars"/';
				$replacement='class="hikashop_vote_stars" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating"';
				$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);
			}

			if(!preg_match('#itemprop="ratingValue"#',$product_page_parts[1])){
				$ratemax=hikaInput::get()->getVar("nb_max_star");//nbmax
				$pattern='/(<span\s+class="hikashop_total_vote")/iUs';
				if(preg_match($pattern,$product_page_parts[1])){

					preg_match('/<input type="hidden" class="hikashop_vote_rating".*data-rate="(.*)"/U',$product_page_parts[1],$matches);
					if(isset($matches[1])){
						$replacement = '<span style="display:none" itemprop="ratingValue">'.$matches[1].'</span><span style="display:none" itemprop="bestRating">'.$ratemax.'</span><span style="display:none" itemprop="worstRating">1</span>$1';
						$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);
					}
					preg_match('/<span class="hikashop_total_vote">.*>(.*)</U',$product_page_parts[1],$matches);
					if(isset($matches[1])){
						$replacement = '<span style="display:none" itemprop="reviewCount">'.trim($matches[1]).'</span>$1';
						$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);
					}
				}else{

					$pattern='#itemtype="https://schema.org/AggregateRating">#';
					preg_match('/class="hk-rating" data-original-title="(.*)"/U',$product_page_parts[1],$matches);
					if(!isset($matches[1]))
						preg_match('/data-rate=".*" data-original-title="(.*)"/U',$product_page_parts[1],$matches);
					if(isset($matches[1])){
						preg_match_all('/<strong>.*<\/strong>(.*)<br\/>/U',$matches[1],$matches);
						if(isset($matches[1][0])){
							$replacement = 'itemtype="https://schema.org/AggregateRating"><span style="display:none" itemprop="ratingValue">'.trim($matches[1][0]).'</span><span style="display:none" itemprop="bestRating">'.$ratemax.'</span><span style="display:none" itemprop="worstRating">1</span>$1';
							$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);
						}
						if(isset($matches[1][1])){
							$replacement = 'itemtype="https://schema.org/AggregateRating"><span style="display:none" itemprop="reviewCount">'.trim($matches[1][1]).'</span>$1';
							$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);
						}
					}
				}
			}

			$pattern='/itemprop="keywords"/';
			$replacement='';
			$product_page_parts[1] = preg_replace($pattern,$replacement,$product_page_parts[1],1);

		}
		foreach($product_page_parts as $parts){
			echo $parts;
		}
	}
}
