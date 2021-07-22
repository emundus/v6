
var RokSlider = new Class({
	Implements: [Events, Options],

	options: {/*
		onChange: $empty,
		onComplete: $empty,*/
		onTick: function(position){
			if(this.options.snap) position = this.toPosition(this.step);
			if (isNaN(position)) position = this.options.offset;
			this.knob.setStyle(this.property, position);
		},
		snap: false,
		offset: 0,
		range: false,
		wheel: false,
		steps: 100,
		mode: 'horizontal'
	},

	initialize: function(element, knob, options){
		this.setOptions(options);
		this.element = document.id(element);
		this.knob = document.id(knob);
		this.previousChange = this.previousEnd = this.step = -1;
		if (this.options.initialize) this.options.initialize.call(this);
		this.element.addEvent('mousedown', this.clickedElement.bind(this));
		if (this.options.wheel) this.element.addEvent('mousewheel', this.scrolledElement.bindWithEvent(this));
		var offset, limit = {}, modifiers = {'x': false, 'y': false};
		switch (this.options.mode){
			case 'vertical':
				this.axis = 'y';
				this.property = 'top';
				offset = 'offsetHeight';
				break;
			case 'horizontal':
				this.axis = 'x';
				this.property = 'left';
				offset = 'offsetWidth';
		}
		this.half = this.knob[offset] / 2;
		this.full = this.element[offset] - this.knob[offset] + (this.options.offset * 2);
		this.min = this.options.range[0] != null ? this.options.range[0] : 0;
		this.max = this.options.range[1] != null ? this.options.range[1] : this.options.steps;
		this.range = this.max - this.min;
		this.steps = this.options.steps || this.full;
		this.stepSize = Math.abs(this.range) / this.steps;
		this.stepWidth = Number((this.stepSize * this.full / Math.abs(this.range)).toFixed(4));
		if (isNaN(this.stepWidth)) this.stepWidth = this.full;
		this.knob.setStyle('position', 'relative').setStyle(this.property, - this.options.offset);
		modifiers[this.axis] = this.property;
		limit[this.axis] = [- this.options.offset, this.full + this.options.offset];
		this.drag = new Drag(this.knob, {
			snap: 0,
			limit: limit,
			modifiers: modifiers,
			onDrag: function() {
				this.draggedKnob();
				this.fireEvent('onDrag', [this.drag.value.now.x]);
			}.bind(this),
			onComplete: function(){
				this.draggedKnob();
				this.end();
			}.bind(this)
		});
		if (this.options.snap) {
			this.drag.options.grid = (this.stepWidth);
			this.drag.options.limit[this.axis][1] = this.full;
		}
	},

	set: function(step){
		if (!((this.range > 0) ^ (step < this.min))) step = this.min;
		if (!((this.range > 0) ^ (step > this.max))) step = this.max;
		this.step = (step);
		this.checkStep();
		this.end(true);
		this.fireEvent('onTick', this.toPosition(this.step));
		return this;
	},

	clickedElement: function(event){
		//event = new Event(event);
		var dir = this.range < 0 ? -1 : 1;
		var position = event.page[this.axis] - this.element.getPosition()[this.axis] - this.half;
		position = position.limit(-this.options.offset, this.full -this.options.offset);

		this.step = (this.min + dir * this.toStep(position));

		this.checkStep();
		this.end();
		this.fireEvent('onTick', position);
	},

	scrolledElement: function(event){
		var mode = (this.options.mode == 'horizontal') ? (event.wheel < 0) : (event.wheel > 0);
		this.set(mode ? this.step - this.stepSize : this.step + this.stepSize);
		event.stop();
	},

	draggedKnob: function(){
		var dir = this.range < 0 ? -1 : 1;
		var position = this.drag.value.now[this.axis];
		position = position.limit(-this.options.offset, this.full -this.options.offset);
		this.step = (this.min + dir * this.toStep(position));

		this.checkStep();
	},

	checkStep: function(){
		this.previousChange = this.step;
		this.fireEvent('onChange', this.step);
	},

	end: function(complete){
		if (this.previousEnd !== this.step){
			this.previousEnd = this.step;
		}

		if (!complete) this.fireEvent('onComplete', this.step + '');
	},

	toStep: function(position){
		var step = (position + this.options.offset) * this.stepSize / this.full * this.steps;
		return this.options.steps ? Math.round(step) : step;
	},

	toPosition: function(step){
		return (this.full * Math.abs(this.min - step)) / (this.steps * this.stepSize) - this.options.offset;
	}

});



Drag.implement({
	drag: function(event){
		this.out = false;
		this.mouse.now = event.page;
		for (var z in this.options.modifiers){
			if (!this.options.modifiers[z]) continue;
			this.value.now[z] = this.mouse.now[z] - this.mouse.pos[z];
			if (this.limit[z]){
				if (this.limit[z][1] != null && (this.value.now[z] > this.limit[z][1])){
					this.value.now[z] = this.limit[z][1];
					this.out = true;
				} else if (this.limit[z][0] != null && (this.value.now[z] < this.limit[z][0])){
					this.value.now[z] = this.limit[z][0];
					this.out = true;
				}
			}

			var position = (this.value.now[z] - (this.limit[z][0]||0)) % this.options.grid[z];
			var halfStep = (position > this.options.grid[z] / 2);

			if (this.options.grid[z]) this.value.now[z] = (halfStep) ? this.value.now[z] - position + this.options.grid[z] : this.value.now[z] - position;
			this.element.setStyle(this.options.modifiers[z], this.value.now[z] + this.options.unit);
		}
		this.fireEvent('onDrag', this.element);
		event.stop();
	}
});
