<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>					<table class="admintable table"  width="100%">
						<tr>
							<td class="key">
								<label for="data[banner][banner_title]">
									<?php echo JText::_( 'HIKA_TITLE' ); ?>
								</label>
							</td>
							<td>
								<input type="text" size="100" name="<?php echo $this->banner_title_input; ?>" value="<?php echo $this->escape(@$this->element->banner_title); ?>" />
								<?php if(isset($this->banner_title_published)){
										$publishedid = 'published-'.$this->banner_title_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->banner_title_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[banner][banner_url]">
									<?php echo JText::_( 'URL' ); ?>
								</label>
							</td>
							<td>
								<input type="text" size="100" name="<?php echo $this->banner_url_input; ?>" value="<?php echo $this->escape(@$this->element->banner_url); ?>" />
								<?php if(isset($this->banner_url_published)){
										$publishedid = 'published-'.$this->banner_url_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->banner_url_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[banner][banner_image_url]">
									<?php echo JText::_( 'IMAGE_URL' ); ?>
								</label>
							</td>
							<td>
								<input type="text" size="100" name="<?php echo $this->banner_image_url_input; ?>" value="<?php echo $this->escape(@$this->element->banner_image_url); ?>" />
								<?php if(isset($this->banner_image_url_published)){
										$publishedid = 'published-'.$this->banner_image_url_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->banner_image_url_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[banner][banner_comment]">
									<?php echo JText::_( 'COMMENT' ); ?>
								</label>
							</td>
							<td>
								<textarea cols="71" name="<?php echo $this->banner_comment_input; ?>" ><?php echo $this->escape(@$this->element->banner_comment); ?></textarea>
								<?php if(isset($this->banner_comment_published)){
										$publishedid = 'published-'.$this->banner_comment_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->banner_comment_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
					</table>
