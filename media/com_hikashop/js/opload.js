/**
 * Opload v1.2.0
 * Copyright (c) 2010-2018 Jerome Glatigny
 */
(function(){
"use strict";

window.oploaders = {};

// name, url, file (input)
var opload = function(name, url, file, options) {
	var t = this, d = document;

	// Clean the name
	t.name = name.replace(/[^-_a-zA-Z0-9]/g, '_');
	t.url = url;

	// Merge options
	t.options = opload.mergeObject({
			'data':null,
			'headers':null,
			'callbacks':{},
			'template':null,
			'method':"POST",
			'hideInput':true,
			'slice':true,
			'nbFiles':3,
			'maxFilenameSize':0,
			'maxPostSize':8388608, //8MB
			'maxChunkSize':8388608, //8MB
		}, options);

	t.options.maxChunkSize = Math.min(t.options.maxChunkSize, t.options.maxPostSize);

	t.filesUpload = (typeof(file) == 'string' ? d.getElementById(file) : file);
	t.filesUpload.setAttribute('tabIndex', '-1');
	t.filesUploadTemplate = t.filesUpload.cloneNode(true);

	if(t.options.drop)
		t.dropArea = d.getElementById( t.options.drop );
	if(t.options.list)
		t.fileList = d.getElementById( t.options.list );

	//
	t.files = {};
	t.fileCounter = 0;

	//
	t.support = {
		'slice': ((typeof(File) === 'function') && File.prototype.slice !== undefined || File.prototype.webkitSlice !== undefined || File.prototype.mozSlice !== undefined),
		'ieVer': -1
	};
	if(navigator.appName == 'Microsoft Internet Explorer' || navigator.userAgent.indexOf("MSIE") !== -1 || navigator.userAgent.indexOf("Trident") !== -1) {
		var ua = navigator.userAgent, re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if(re.exec(ua) != null)
			t.support.ieVer = parseFloat(RegExp.$1);
		if(navigator.userAgent.indexOf("rv:11") !== -1)
			t.support.ieVer = 11;
	}

	t.init();
	window.oploaders[t.name] = t;
	return t;
};
/**
 *
 */
opload.prototype = {
	/**
	 *
	 */
	init: function() {
		var t = this;
		t.initInput();
		if(t.dropArea && t.dropArea.addEventListener && (t.support.ieVer < 0 || t.support.ieVer > 9.0)) {
			t.initDrop();
		} else if(t.dropArea && !t.options.forceDrop) {
			t.dropArea.style.display = 'none';
		}
	},
	/**
	 *
	 */
	upload: function(file) {
		var d = document, t = this,
			entryId = t.fileCounter++,
			entry = {
				'div': d.createElement("div"),
				'xhr': null,
				'id': entryId,
				'slices': 0
			};

		// Get upload block content
		entry.div.innerHTML = t.getTemplate(entryId, t.getFilename(file.name), opload.getSize(file.size));

		// Handle thumbnail
		t.handleThumbnail(entry, file);

		if(t.options.slice && t.support.slice) {
			entry.slices = Math.ceil(file.size / t.options.maxChunkSize);
			entry.index = 0;
		} else if(t.options.maxPostSize < file.size) {
			t.error(entry, 'Limit: ' + opload.getSize(t.options.maxPostSize));
			t.files[entryId] = entry;
			t.fileList.appendChild(entry.div);
			return false;
		}

		t.sendSlice(entry, file);

		//
		t.files[entryId] = entry;
		t.fileList.appendChild(entry.div);
	},
	/**
	 *
	 */
	initXHR: function(entry, file, settings) {
		var t = this;
		if(!settings) settings = {};

		entry.xhr = new XMLHttpRequest();

		// Update progress bar
		entry.xhr.upload.addEventListener("progress", function(evt){
			opload.xhrFileProgress(evt, entry);
		}, false);

		// File uploaded
		entry.xhr.addEventListener("load", function(){
			t.xhrFileLoaded(entry, file);
		});

		var params = {
			'method': settings.method || t.options.method || "POST",
			'url': settings.url || t.url,
	//		'data': t.options.data || {},
			'headers': settings.headers || t.options.headers || {},
			'user': settings.user || null,
			'password': settings.password || null
		};
		//
		t.triggerEvent('onInitXHR',{'entry':entry,'file':file,'params':params});
		if(t.options.callbacks.before)
			t.options.callbacks.before(entry, file, params, t);

		//
		entry.xhr.open(params.method, params.url, true, params.user, params.password);

		//
		if(!params.headers || params.headers.length == 0)
			return;
		for(var k in params.headers) {
			if(params.headers[k] && params.headers.hasOwnProperty(k))
				entry.xhr.setRequestHeader(k, params.headers[k]);
		}
	},
	/**
	 *
	 */
	getXhrData: function(entry, file, extradata) {
		var t = this, fd = new FormData(),
			filename = entry.file_name || file.name,
			fileInputName = t.options.fileInputName || filename;

		if(t.options.data) {
			for(var k in t.options.data) {
				if(t.options.data[k] && t.options.data.hasOwnProperty(k))
					fd.append(k, t.options.data[k]);
			}
		}
		if(extradata) {
			for(var k in extradata) {
				if(extradata[k] && extradata.hasOwnProperty(k))
					fd.append(k, extradata[k]);
			}
		}

		if(!entry.slices || entry.slices <= 1) {
			fd.append(fileInputName, file, filename);
			return fd;
		}

		if(!entry.chunk_size) entry.chunk_size = t.options.maxChunkSize;

		var start = entry.chunk_size * entry.index,
			end = start + entry.chunk_size;
		if(end >= file.size)
			end = file.size;
		fd.append(fileInputName, opload.sliceBlob(file, start, end), filename);
		fd.append('filename', filename);
		fd.append('slice', entry.index);
		fd.append('slice_size', entry.chunk_size);
		fd.append('slices', entry.slices);
		fd.append('slices_size', file.size);

		return fd;
	},
	/**
	 *
	 */
	xhrFileLoaded: function(entry, file) {
		var t = this,
			response = t.getXhrResponse(entry);

		var item = (opload.getElement(entry, 'item') || entry.div),
			bar = opload.getElement(entry, 'bar'),
			percent = opload.getElement(entry, 'percent');

		// Send the next slice if required
		if(response && !response.error && entry.slices > 1 && (entry.index + 1) < entry.slices) {
			entry.index++;

			var p = Math.round((100 / entry.slices) * entry.index) + "%";
			if(bar)
				bar.style.width = p;
			if(percent)
				percent.innerHTML = p;

			entry.file_name = file.name;
			if(response && response.name)
				entry.file_name = response.name;

			// Handle upload resume
			if(response && response.resume && response.slice && response.slice > entry.index && response.slice < entry.slices)
				entry.index = response.slice;

			t.sendSlice(entry, file);
			return;
		}
		t.sendNextFile();
		if(percent)
			percent.innerHTML = '100%';
		// Update the progress bar
		if(bar) {
			opload.addClass(item, (response && !response.error) ? 'oploadFinish' : 'oploadError');
			if(response && response.error && percent)
				percent.innerHTML = ' Error: ' + response.error;
			bar.style.width = '100%';
			opload.removeClass(bar, 'active');
		}
		entry.status = 1;
	},
	/**
	 *
	 */
	getXhrResponse: function(entry) {
		var t = this, response = true;
		if(!entry.xhr.responseText || entry.xhr.responseText.length == 0 || entry.xhr.responseText == '0')
			response = false;

		try {
			var tmp = JSON.parse(entry.xhr.responseText);
			response = tmp;
		} catch(e) { }

		//
		t.triggerEvent('onXhrLoaded',{'entry':entry,'response':response});
		if(t.options.callbacks && t.options.callbacks.done)
			response = t.options.callbacks.done(entry);
		return response;
	},
	/**
	 *
	 */
	sendSlice: function(entry, file) {
		var t = this;
		// Init the XHR
		t.initXHR(entry, file);
		// Process the data
		var data = t.getXhrData(entry, file);
		//
		if(!entry.slices || entry.slices <= 1 || !entry.index || entry.index === 0) {
			if(!entry.pending && t.haveOpenUploadSlot()) {
				entry.xhr.send(data);
				entry.status = 0;
			} else {
				entry.fd = data;
				entry.status = -1;
			}
		} else {
			if(!entry.pending)
				entry.xhr.send(data);
			else
				entry.fd = data;
		}
	},
	/**
	 *
	 */
	sendNextFile: function() {
		var t = this;
		if(!t.options.nbFiles || t.options.nbFiles < 1)
			return;
		for(var i = 0; i < t.files.length; i++) {
			if(!t.files[i] || !t.files[i].fd || t.files[i].status != -1 || t.files[i].pending)
				continue;
			t.files[i].xhr.send(t.files[i].fd);
			t.files[i].status = 0;
		}
	},
	/**
	 *
	 */
	getTemplate: function(idx, filename, filesize) {
		// Present file info and append it to the list of files
		var template = this.options.template || '<div data-opload="item" data-opload-name="{NAME}" data-opload-id="{ID}" id="opload_{NAME}_{ID}" class="oploadQueueItem">'+
			'<div data-opload="thumbnail" class="oploadThumb"></div><a class="oploadCancel" href="#cancel" data-opload="cancel" onclick="window.oploaders[\'{NAME}\'].cancel(this, {ID}); return false;">×</a>'+
			'<span data-opload="filename" class="fileName">{FILENAME} ({FILESIZE})</span><span data-opload="percent" class="oploadPercentage"></span>'+
			'<div class="oploadProgress"><div data-opload="bar" class="oploadProgressBar active"></div></div></div>';
		return template
			.replace(/{NAME}/g, this.name)
			.replace(/{ID}/g, idx)
			.replace(/{FILENAME}/g, filename)
			.replace(/{FILESIZE}/g, filesize);
	},
	getFilename: function(filename) {
		var t = this;
		if(!t.options.maxFilenameSize || filename.length <= t.options.maxFilenameSize)
			return filename;
		if(!t.options.truncateBegin) t.options.truncateBegin = 12;
		if(!t.options.truncateEnd) t.options.truncateEnd = 6;
		return filename.substring(0, t.options.truncateBegin) + '...' + filename.substring(filename.length - t.options.truncateEnd);
	},
	/**
	 *
	 */
	haveOpenUploadSlot: function() {
		var t = this;
		if(!t.options.nbFiles || t.options.nbFiles < 1)
			return true;
		var cpt = 0;
		for(var i = t.files.length - 1; i >= 0; i--) {
			if(t.files[i].status == 0)
				cpt++;
		}
		return (cpt < t.options.nbFiles);
	},
	/**
	 *
	 */
	handleThumbnail: function(entry, file) {
		if(typeof(FileReader) === "undefined" || !(/image/i).test(file.type) || file.size > 2097152)
			return;
		var thumb = opload.getElement(entry, 'thumb');
		if(!thumb)
			return;
		var img = document.createElement("img");
		thumb.appendChild(img);
		reader = new FileReader();
		reader.onload = (function(i){
			return function(evt) {
				i.src = evt.target.result;
			};
		}(img));
		reader.readAsDataURL(file);
	},
	/**
	 *
	 */
	error: function(entry, msg) {
		entry.status = 1;

		var item = (opload.getElement(entry, 'item') || entry.div),
			bar = opload.getElement(entry, 'bar'),
			percent = opload.getElement(entry, 'percent');
		opload.addClass(item, 'oploadError');
		if(bar) {
			bar.style.width = '100%';
			opload.removeClass(bar, 'active');
		}
		if(percent)
			percent.innerHTML = msg;
	},
	/**
	 *
	 */
	triggerEvent: function(name, params) {
		var t = this;
		if(!t.filesUpload || !t.filesUpload.dispatchEvent)
			return;
		var event = new CustomEvent(name, params);
		t.filesUpload.dispatchEvent(event);
	},
	/**
	 *
	 */
	initInput: function() {
		var t = this;
		if(t.filesUpload.addEventListener) {
			t.filesUpload.addEventListener("change", function(){
				t.traverseFiles(this.files, this);
			}, false);
		} else {
			t.filesUpload.attachEvent("onchange", function(){
				t.traverseFiles(this.files, t.filesUpload);
			});
		}
		if(t.support && t.support.ieVer > 0 && t.support.ieVer <= 8.0)
			t.hideInputIE();
	},
	/**
	 *
	 */
	initDrop: function() {
		var t = this;

		t.dropArea.addEventListener("dragleave", function(evt){
			var d = document,
				target = evt.target;
			var cursorTarget = d.elementFromPoint(evt.clientX, evt.clientY);
			if(!cursorTarget || cursorTarget === t.dropArea)
				return;
			var p = cursorTarget.parentNode;
			while(p) {
				if(p === t.dropArea) {
					opload.addClass(this, "opload-drop-over");
					return;
				}
				p = p.parentNode;
			}
			opload.removeClass(this, "opload-drop-over");
//			opload.cancelEvent(evt);
		}, false);

		t.dropArea.addEventListener("dragenter", function(evt){
			opload.addClass(this, "opload-drop-over");
			opload.addClass(this, "opload-drop-zone");
//			opload.cancelEvent(evt);
		}, false);

		t.dropArea.addEventListener("dragover", function(evt){
			if(evt.dataTransfer)
				evt.dataTransfer.dropEffect = (evt.dataTransfer.effectAllowed === "move") ? "move" : "copy";
			opload.cancelEvent(evt);
		}, false);

		t.dropArea.addEventListener("drop", function(evt){
			try {
				if(evt.dataTransfer.files) {
					t.traverseFiles(evt.dataTransfer.files);
				} else {
					var url = evt.dataTransfer.getData('URL');
					//
					t.triggerEvent('onGetSource',{'entry':url,'ref':evt});
					if(url && t.options.callbacks && t.options.callbacks.source)
						t.options.callbacks.source(url, evt);
				}
				opload.removeClass(this, "opload-drop-over");
			} catch(e) {
				console.log(e);
			}
			opload.cancelEvent(evt);
		}, false);

		if(t.options.hideInput) {
			t.filesUpload.style.display = 'none';
			t.filesUploadTemplate.style.display = 'none';
		}
	},
	/**
	 *
	 */
	traverseFiles: function(files, input) {
		var t = this, c = t.filesUpload.parentNode;
		if(typeof files !== "undefined") {
			for(var i = 0, l = files.length; i < l; i++) {
				t.upload(files[i]);
			}
		} else {
			t.uploadFallback(input);
		}
		if(c) {
			try{
				c.removeChild(t.filesUpload);
			}catch(e){}
		}
		t.filesUpload = null;
		t.filesUpload = t.filesUploadTemplate.cloneNode(true);
		c.appendChild(t.filesUpload);
		t.initInput();
	},
	/**
	 *
	 */
	cancel: function(el, id) {
		if(!this.files[id])
			return;
		var entry = this.files[id];
		if(entry.status == 1 || !entry.xhr) {
			if(entry.div && entry.div.parentNode)
				entry.div.parentNode.removeChild(entry.div);
			this.files[id] = null;
		}
		if(entry.status == 0)
			entry.xhr.abort();
	},
	/**
	 *
	 */
	add: function(el) {
		this.filesUpload.click();
		return false;
	},
	/**
	 *
	 */
	hideInputIE: function() {
		var d = document,
			btn = d.getElementById(t.filesUpload.getAttribute('id') + '-btn');
		if(!btn)
			btn = d.getElementById(t.name + '-btn');
		if(btn) {
			btn.style.position = 'relative';
			btn.style.overflow = 'hidden';
		}
		var s = t.filesUpload.style;
		s.position = 'absolute'; s.top = '0px'; s.right = '0px'; s.margin = '0px';
		s.opacity = '0'; s.filter = 'alpha(opacity=0)'; s.cursor = 'hand';
	},
	/**
	 * TODO
	 */
	uploadFallback: function(input) {
		var gen = function(el, attr) {
			var e = document.createElement(el);
			for(var k in attr) {
				if(!attr.hasOwnProperty(k))
					continue;
				e[k] = attr[k];
			}
			return e;
		};

		var d = document,
			t = this,
			entryId = t.fileCounter++,
			entry = {
				div: d.createElement("div"),
				status: 0,
				input: input,
				id: entryId
			},
			iframeOnLoad = null,
			filename = 'noname';
		if(input && input.value)
			filename = input.value.replace(/.*(\/|\\)/, "");
		entry.div.innerHTML = t.getTemplate(entryId, t.getFilename(filename), opload.getSize(0));

		var params = {
			'method': t.options.method || "POST",
			'url': t.url,
			'headers': t.headers || {},
	//		'data': t.options.data || {},
			'user': null,
			'password': null,
			'fallback': true
		};
		//
		t.triggerEvent('onInitXHR',{'entry':entry,'file':null,'params':params});
		if(t.options.callbacks.before)
			t.options.callbacks.before(entry, null, params);

		var n = 'opload_fallback_'+entryId,
			iframe = gen('iframe', {
				'src':'javascript:false;',
				'id':n,
				'name':n,
				'style':'display:none;'
			}),
			form = gen('form',{
				'method':params.method,
				'enctype':'multipart/form-data',
				'target':n,
				'action':params.url,
				'style':'display:none;position:absolute;left:-9999px;top:-9999px;'
			});
		form.appendChild( gen('input', {'type':'submit'}) );
		form.appendChild( gen('input', {'type':'hidden','name':'opload-mode','value':'fallback'}) );
		if(t.options.data) {
			for(var k in t.options.data) {
				if(!t.options.data[k] | !t.options.data.hasOwnProperty(k))
					continue;
				form.appendChild( gen('input', {'type':'hidden','name':k,'value':t.options.data[k]}) );
			}
		}

		input.name = t.options.fileInputName || entry.file_name || ('file_' + entryId);

		form.appendChild(input);
		d.body.appendChild(iframe);
		d.body.appendChild(form);

		// Attach "load" event to the form
		var iframeOnLoad = function() {
			if(!iframe.parentNode) return;
			var response = false;
			try{
				if(iframe.contentDocument && iframe.contentDocument.body && iframe.contentDocument.body.innerHTML == 'false')
					return;
				// Upload is finished : Get iframe content
				var doc = iframe.contentDocument ? iframe.contentDocument: iframe.contentWindow.document,
					innerHTML = doc.body.innerHTML;
				response = true;
				if(!innerHTML || innerHTML.length == 0 || innerHTML == '0')
					response = false;
				t.triggerEvent('onXhrLoaded',{'entry':entry,'fallback':innerHTML,'response':response});
				if(t.callbacks && t.callbacks.done)
					response = t.callbacks.done(entry, innerHTML);
			}catch(e){};

			entry.status = 1;
			entry.input = null;

			var item = (opload.getElement(entry, 'item') || entry.div),
				bar = opload.getElement(entry, 'bar'),
				percent = opload.getElement(entry, 'percent');
			if(bar) {
				opload.addClass(item, (response && !response.error) ? 'oploadFinish' : 'oploadError');
				if(response && response.error && percent)
					percent.innerHTML = ' Error: ' + response.error;
				bar.style.width = '100%';
				opload.removeClass(bar, 'active');
			}
			input.name = '';
			input.value = '';

			setTimeout(function(){
				if(iframe.removeEventListener)
					iframe.removeEventListener('load', iframeOnLoad);
				else
					iframe.detachEvent('onload', iframeOnLoad);
				iframe.parentNode.removeChild(iframe);
			}, 10);
		};
		if(iframe.addEventListener)
			iframe.addEventListener('load', iframeOnLoad, false);
		else
			iframe.attachEvent('onload', iframeOnLoad);

		// Send file
		form.submit();
		form.parentNode.removeChild(form);

		t.files[entryId] = entry;
		t.fileList.appendChild(entry.div);
	}
};
/**
 *
 */
opload.xhrFileProgress = function(evt, entry) {
	// No data for processing
	if(!evt.lengthComputable)
		return;
	var p = Math.round((evt.loaded / evt.total) * 100) + "%";
	if(entry.slices > 1)
		p = Math.round((100 / entry.slices) * (entry.index + (evt.loaded / evt.total))) + "%";
	var bar = opload.getElement(entry, 'bar'),
		percent = opload.getElement(entry, 'percent');
	if(bar)
		bar.style.width = p;
	if(percent)
		percent.innerHTML = p;
};
/**
 *
 */
opload.mergeObject = function(a, b) {
	if(Object && Object.assign)
		return Object.assign(a, b);
	var to = Object(a);
	for (var k in b) {
		if(Object.prototype.hasOwnProperty.call(b, k))
			to[k] = b[k];
	}
	return to;
};
opload.cancelEvent = function(evt) {
	evt.preventDefault();
	evt.stopPropagation();
};
opload.sliceBlob = function(content, begin, end) {
	var s = content.slice || content.mozSlice || content.webkitSlice;
	return s.call(content, begin, end);
};
/**
 *
 */
opload.getElement = function(entry, name) {
	if(!entry.div) return false;
	return entry.div.querySelector('[data-opload="'+name+'"]');
};
opload.getSize = function(size) {
	if(size >= 1073741824)
		return (size / 1073741824).toFixed(2) + '&nbsp;GB';
	if(size >= 1048576)
		return (size / 1048576).toFixed(2) + '&nbsp;MB';
	return (size / 1024).toFixed(2) + '&nbsp;KB';
};
/**
 *
 */
opload.hasClass = function(o,n) {
	if(o.classList && o.classList.contains) return o.classList.contains(n);
	if(o.className == '' ) return false;
	var reg = new RegExp("(^|\\s+)"+n+"(\\s+|$)");
	return reg.test(o.className);
};
opload.addClass = function(o,n) {
	if(o.classList && o.classList.add) return o.classList.add(n);
	if(this.hasClass(o,n)) return;
	if(o.className == '') o.className = n;
	else o.className += ' '+n;
};
opload.trim = function(s) {
	if(s.trim) return s.trim();
	return (s ? '' + s : '').replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
};
opload.removeClass = function(e, c) {
	if(e.classList && e.classList.remove) return e.classList.remove(c);
	if(!this.hasClass(e,c)) return;
	var cn = ' ' + e.className + ' ';
	e.className = this.trim(cn.replace(' '+c+' ',' '));
};
opload.toggleClass = function(e,c) {
	if(e.classList && e.classList.toggle) return e.classList.toggle(c);
	var t = this;
	if(t.hasClass(e,c)) return t.removeClass(e,c);
	return t.addClass(e,c);
};

opload.version = 20180530;
if(!window.opload || window.opload.version < opload.version)
	window.opload = opload;

})();

/**
 * HikaShop Opload bridge
 * Copyright (c) 2013-2018 Obsidev S.A.R.L.
 */
(function() {

if(window.jQuery && typeof(jQuery.noConflict) == "function" && !window.hkjQuery)
	window.hkjQuery = jQuery.noConflict();

var hkUploaderList = [];
window.hkUploaderList = hkUploaderList;

/**
 *
 */
var hkUploaderMgr = function(id, data) {
	var t = this;
	t.id = id;
	t.url = data.url;
	t.drop = data.drop || (id + '_main');
	t.formData = data.formData || null;
	t.mediaPath = data.mediaPath;
	t.idList = data.idList || (id + '_list');
	t.mode = data.mode || 'single';
	t.imageClickBlocked = false;

	t.options = {
		'maxFilenameSize': 20,
		'truncateBegin': 12,
		'truncateEnd': 6
	};
	if(data.options) {
		for(var o in data.options) {
			if(data.options.hasOwnProperty(o))
				t.options[o] = data.options[o];
		}
	}

	window.hkUploaderList[id] = this;
	t.initUploader();
};

/**
 *
 */
hkUploaderMgr.prototype = {
	/**
	 *
	 */
	initUploader: function() {
		var t = this, d = document;
			dest = d.getElementById(t.id);
		if(!dest)
			return false;

		t.oploader = new window.opload(t.id, t.url, t.id, {
			drop:t.drop,
			list:t.idList,
			data:t.formData,
			forceDrop:true,
			maxSize:t.options.maxSize,
			maxPostSize:t.options.maxPostSize || t.options.maxSize || 2097152, // 2MB
			maxFilenameSize: t.options.maxFilenameSize,
			truncateBegin: t.options.truncateBegin,
			truncateEnd: t.options.truncateEnd,
			callbacks:{
				done: function(entry, fallback){ return t._doneCallback(entry, fallback); }
			}
		});

		if(t.mode == 'listImg') {
			hkjQuery('#'+t.id+'_content').sortable({
				cursor: "move",
				stop: function(event, ui) {
					var f = hkjQuery('#'+t.id+'_content').children("li").first();
					if(t.options['imgClasses'] && t.options['imgClasses'][1]) {
						if(f.hasClass(t.options['imgClasses'][1])) {
							hkjQuery('#'+t.id+'_content .'+t.options['imgClasses'][0]).removeClass("hikashop_product_main_image_thumb").addClass(t.options['imgClasses'][1]);
							f.removeClass(t.options['imgClasses'][1]).addClass(t.options['imgClasses'][0]);
						}
					}
					t.imageClickBlocked = true; // Firefox trick
					setTimeout(function(){ t.imageClickBlocked = false; }, 150);
				}
			});
			hkjQuery('#'+t.id+'_content').disableSelection();
		}
		window.hkUploaderList[t.id].uploadFile = function(el) {
			var dest = document.getElementById(t.id);
			dest.click();
			return false;
		};

		return true;
	},
	_doneCallback: function(entry, fallback) {
		var t = this, d = document,
			response = fallback, dest = d.getElementById(t.id);
		if(!response && entry.xhr && entry.xhr.responseText)
			response = entry.xhr.responseText;

		if(!response || response.length == 0 || response == '0')
			return false;

		try	{
			response = window.Oby.evalJSON(response);
		}catch(e) { response = false; }
		if(!response)
			return false;

		if(response.partial || response.error)
			return response;

		if((response.name || response.type) && !response[0])
			response = [ response ];

		for(var i = 0; i < response.length; i++) {
			var r = response[i];
			if(t.mode == 'single' && r.html && r.html.length > 0) {
				var dest = d.getElementById(t.id+'_content');
				if(dest) dest.innerHTML = r.html;
				var empty = d.getElementById(t.id+'_empty');
				if(empty) empty.style.display = 'none';
				t.oploader.options.data.uploader_oldname = r.name;
			} else if(t.mode == 'listImg') {
				if(t.options['imgClasses'] && t.options['imgClasses'][1]) {
					var dest = hkjQuery('#'+t.id+'_content'), myData = document.createElement('li'), className = '';
					className = t.options['imgClasses'][1];
					if(dest.children().length == 0)
						className = t.options['imgClasses'][0];
				}
				if(r.html && r.html.length > 0) {
					hkjQuery(myData).addClass(className).html(r.html).appendTo( dest );
					hkjQuery('#'+t.id+'_empty').hide();
				}
			} else if(t.mode == 'list' && r.html && r.html.length > 0) {
				var dest = d.getElementById(t.id+'_content'), myData = document.createElement('div');
				hkjQuery(myData).html(r.html).appendTo( dest );
				hkjQuery('#'+t.id+'_empty').hide();
			}
		}
		setTimeout(function(){
			if(entry && entry.id !== undefined)
				window.oploaders[t.id].cancel(null, entry.id);
		}, 1000);

		if(typeof(response) == 'object' && response[0])
			return response[0];
		return response;
	},
	/**
	 *
	 */
	uploadFile: function(el) {
		var t = this, h = window.hikashop;
		if(t.uploadFilePopup)
			return t.uploadFilePopup(el);
		h.submitFct = function(data) { t._receiveFile(data); };
		h.openBox(el,null);
		return false;
	},
	/**
	 *
	 */
	browseImage: function(el) {
		var t = this, h = window.hikashop;
		if(t.browseImagePopup)
			return t.browseImagePopup(el);
		h.submitFct = function(data) { t._receiveFile(data); };
		h.openBox(el,null);
		return false;
	},
	/**
	 *
	 */
	genericButtonClick: function(el) {
		var t = this, h = window.hikashop;
		h.submitFct = function(data) { t._receiveFile(data); };
		h.openBox(el,null);
		return false;
	},
	/**
	 *
	 */
	_receiveFile: function(data) {
		if(!data || !data.images)
			return;
		var t = this, added = false;
		if(t.mode == 'single') {
			var dest = hkjQuery('#'+t.id+'_content'), r = data.images[0];
			if(r && r.length > 0) {
				dest.html(r);
				added = true;
			}
		} else if(t.mode == 'listImg') {
			var dest = hkjQuery('#'+t.id+'_content'), className = '', r;
			for(var i = 0; i < data.images.length; i++) {
				if(t.options['imgClasses'] && t.options['imgClasses'][1]) {
					className = t.options['imgClasses'][1];
					if(dest.children().length == 0)
						className = t.options['imgClasses'][0];
				}
				r = data.images[i];
				if(r && r.length > 0) {
					var myData = document.createElement('li');
					hkjQuery(myData).addClass(className).html(r).appendTo( dest );
					added = true;
				}
			}
		}
		if(added)
			hkjQuery('#'+t.id+'_empty').hide();
	},
	/**
	 *
	 */
	delImage: function(el, field) {
		var li = el.parentNode, d = document, ul = li.parentNode, empty = d.getElementById(this.id+'_empty');
		if(field === undefined) {
			var child = false;
			window.hikashop.deleteId(li);
			while(ul && !window.Oby.hasClass(ul, 'uploader_data_container'))
				ul = ul.parentNode;
			for(var i = ul.childNodes.length - 1; i >= 0; i--) {
				if(ul.childNodes[i].nodeType == 1) {
					child = true;
					break;
				}
			}
			if(!child && empty) {
				empty.style.display = '';
			}
		} else {
			window.hikashop.deleteId(li);
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = field;
			input.value = '';
			ul.appendChild(input);
			if(empty)
				empty.style.display = '';
		}
		return false;
	},
	delBlock: function(el, field) { return this.delImage(el,field); }
};
window.hkUploaderMgr = hkUploaderMgr;

})();
