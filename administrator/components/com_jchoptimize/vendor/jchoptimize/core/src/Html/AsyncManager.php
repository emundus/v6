<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Html;

use JchOptimize\Core\DynamicJs;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Utility;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );


class AsyncManager
{
	protected $oParams;
	protected $sLnEnd;
	protected $sOnUserInteractFunction = '';
	protected $sLoadCssOnUIFunction = '';
	protected $sLoadScriptOnUIFunction = '';
	protected $sLoadReduceDomFunction = '';

	public function __construct( Settings $oParams, $sLnEnd )
	{
		$this->oParams = $oParams;
		$this->sLnEnd  = $sLnEnd;
	}

	public function loadCssAsync( $aCssUrls )
	{
		$this->loadOnUIFunction();

		$sNoScriptUrls = implode( Utility::lnEnd(), array_map( function ( $sUrl ) {
			//language=HTML
			return '<link rel="stylesheet" href="' . $sUrl . '" />';
		}, $aCssUrls ) );

		$aJsonEncodedUrlArray = $this->jsonEncodeUrlArray( $aCssUrls );

		$this->sLoadCssOnUIFunction = <<<HTML
<script>
let jch_css_loaded = false;

onUserInteract(function(){ 
	var css_urls = {$aJsonEncodedUrlArray};
        
	if (!jch_css_loaded){
	    	css_urls.forEach(function(url, index){
	       		let l = document.createElement('link');
			l.rel = 'stylesheet';
			l.href = url;
			let h = document.getElementsByTagName('head')[0];
			h.append(l); 
	    	});
	    
		jch_css_loaded = true;
        }
});
</script>
<noscript>
{$sNoScriptUrls}
</noscript>
HTML;

	}

	private function loadOnUIFunction()
	{
		$this->sOnUserInteractFunction = <<<HTML
<script>
function onUserInteract(callback) { 
	window.addEventListener('load', function() {
	        if (window.pageYOffset !== 0){
	        	callback()
	        }
	});
	
	window.addEventListener('scroll', function() {
	        callback()
	});
	
	document.addEventListener('DOMContentLoaded', function() {
		let b = document.getElementsByTagName('body')[0];
		b.addEventListener('mouseenter', function() {
	        	callback()
		});
	});
}

</script>
HTML;
	}

	private function jsonEncodeUrlArray( $aUrls )
	{
		$aHtmlDecodedUrls = array_map( function ( $mUrl ) {

			if ( is_array( $mUrl ) )
			{
				$mUrl['url'] = html_entity_decode( $mUrl['url'] );

				return $mUrl;
			}

			return html_entity_decode( $mUrl );
		}, $aUrls );

		return json_encode( $aHtmlDecodedUrls );
	}

	public function printHeaderScript()
	{
		$this->loadJsDynamic( DynamicJs::$aJsDynamicUrls );
		$this->loadReduceDom();

		return $this->sOnUserInteractFunction . $this->sLnEnd .
			$this->sLoadCssOnUIFunction . $this->sLnEnd .
			$this->sLoadScriptOnUIFunction . $this->sLnEnd .
			$this->sLoadReduceDomFunction;
	}

	public function loadJsDynamic( $aJsUrls )
	{
		if ( $this->oParams->get( 'pro_remove_unused_js_enable', '0' ) &&
			! empty( $aJsUrls ) )
		{
			$this->loadOnUIFunction();

			$aJsonEncodedUrlArray = $this->jsonEncodeUrlArray( $aJsUrls );

			$this->sLoadScriptOnUIFunction = <<<HTML
<script>
let jch_js_loaded = false;

onUserInteract( function(){
   	let js_urls = {$aJsonEncodedUrlArray} 
   	
   	const loadScripts = function (js_array){
   	    	if (js_array.length >= 1){
   	    	    
   	    	    	let js_obj = js_array.shift();
			let j = document.createElement('script');
			
			if ('noModule' in HTMLScriptElement.prototype && js_obj.nomodule){
			    js_array.shift();
			    loadScripts(js_array);
			    return;
			}
			
			if (!'noModule' in HTMLScriptElement.prototype && js_obj.module){
			    js_array.shift();
			    loadScripts(js_array);
			    return;
			}
			   
			j.onload = function (){
			    loadScripts(js_array);
			}
			
			j.setAttribute('src', js_obj.url);
				
			if (js_obj.module){
				j.setAttribute('type', 'module');
			}
			
			if (js_obj.nomodule){
				j.setAttribute('nomodule', '');
			}
			
			let h = document.getElementsByTagName('head')[0];
			h.append(j); 
   	    	}
   	};
   	
   	    	
   	if (!jch_js_loaded){
		
   	    	loadScripts(js_urls);
   	    	
   	    	jch_js_loaded = true;
   	    	
   	    	window.dispatchEvent(new Event('DOMContentLoded'));
   	    	window.dispatchEvent(new Event('load'));
   	}
});
</script>
HTML;

		}
	}

	public function loadReduceDom()
	{
		if ( $this->oParams->get( 'pro_reduce_dom', '0' ) )
		{
			$this->loadOnUIFunction();

			$this->sLoadReduceDomFunction = <<<HTML
<script>
onUserInteract(function(){
	let containers = document.getElementsByClassName('jch-reduced-dom-container');
	
	if (containers.length > 0){
		for (let container of containers){
		    	//First child should be templates with content attribute
		      	const template  = container.firstChild; 
		      	//clone template
		      	const clone = template.content.firstElementChild.cloneNode(true);
		      	//replace container with content
		      	container.parentNode.replaceChild(clone, container);
		}
		
		//dispatchEvent(new Event('load'));
	}
});

</script>
HTML;
		}
	}
}