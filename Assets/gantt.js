// Based on jQuery.ganttView v.0.8.8 Copyright (c) 2010 JC Grubbs - jc.grubbs@devmynd.com - MIT License
var GanttUtils = function () {
	this.data = [];

	this.options = {
		container: '#gantt-chart',
		showWeekends: true,
		showToday: true,
		allowMoves: true,
		allowResizes: true,
		cellWidth: 21,
		cellHeight: 31,
		slideWidth: 1000,
		vHeaderWidth: 200,
	};
};

// Save record after a resize or move
GanttUtils.prototype.saveRecord = function (record) {
	$.ajax({
		cache: false,
		url: $(this.options.container).data('save-url'),
		contentType: 'application/json',
		type: 'POST',
		processData: false,
		data: JSON.stringify(record),
	});
};

// Build the Gantt chart
GanttUtils.prototype.show = function () {
	this.data = this.prepareData($(this.options.container).data('records'));

	var minDays = Math.floor(this.options.slideWidth / this.options.cellWidth + 5);
	var range = this.getDateRange(minDays);
	var startDate = range[0];
	var endDate = range[1];
	var container = $(this.options.container);
	var chart = jQuery('<div>', { class: 'ganttview' });

	chart.append(this.renderVerticalHeader());
	chart.append(this.renderSlider(startDate, endDate));
	container.append(chart);

	jQuery('div.ganttview-grid-row div.ganttview-grid-row-cell:last-child', container).addClass(
		'last',
	);
	jQuery('div.ganttview-hzheader-days div.ganttview-hzheader-day:last-child', container).addClass(
		'last',
	);
	jQuery(
		'div.ganttview-hzheader-months div.ganttview-hzheader-month:last-child',
		container,
	).addClass('last');

	if (!$(this.options.container).data('readonly')) {
		this.listenForBlockResize(startDate);
		this.listenForBlockMove(startDate);
	} else {
		this.options.allowResizes = false;
		this.options.allowMoves = false;
	}
};

// Render record list on the left
GanttUtils.prototype.renderVerticalHeader = function () {
	var headerDiv = jQuery('<div>', { class: 'ganttview-vtheader' });
	var itemDiv = jQuery('<div>', { class: 'ganttview-vtheader-item' });
	var seriesDiv = jQuery('<div>', { class: 'ganttview-vtheader-series' });

	for (var i = 0; i < this.data.length; i++) {
		var content = jQuery('<span>')
			.append(this.infoTooltip(this.getVerticalHeaderTooltip(this.data[i])))
			.append('&nbsp;');

		if (this.data[i].type == 'task') {
			content.append(jQuery('<strong>').text('#' + this.data[i].id + ' '));
			content.append(
				jQuery('<a>', { href: this.data[i].link, title: this.data[i].title }).text(
					this.data[i].title,
				),
			);
		} else {
			content
				.append(
					jQuery('<a>', {
						href: this.data[i].board_link,
						title: $(this.options.container).data('label-board-link'),
					}).append('<i class="fa fa-th"></i>'),
				)
				.append('&nbsp;')
				.append(
					jQuery('<a>', {
						href: this.data[i].gantt_link,
						title: $(this.options.container).data('label-gantt-link'),
					}).append('<i class="fa fa-sliders"></i>'),
				)
				.append('&nbsp;')
				.append(jQuery('<a>', { href: this.data[i].link }).text(this.data[i].title));
		}

		seriesDiv.append(jQuery('<div>', { class: 'ganttview-vtheader-series-name' }).append(content));
	}

	itemDiv.append(seriesDiv);
	headerDiv.append(itemDiv);

	return headerDiv;
};

// Render right part of the chart (top header + grid + bars)
GanttUtils.prototype.renderSlider = function (startDate, endDate) {
	var slideDiv = jQuery('<div>', { class: 'ganttview-slide-container' });
	var dates = this.getDates(startDate, endDate);

	slideDiv.append(this.renderHorizontalHeader(dates));
	slideDiv.append(this.renderGrid(dates));
	slideDiv.append(this.addBlockContainers());
	this.addBlocks(slideDiv, startDate);

	return slideDiv;
};

// Render top header (days)
GanttUtils.prototype.renderHorizontalHeader = function (dates) {
	var headerDiv = jQuery('<div>', { class: 'ganttview-hzheader' });
	var monthsDiv = jQuery('<div>', { class: 'ganttview-hzheader-months' });
	var daysDiv = jQuery('<div>', { class: 'ganttview-hzheader-days' });
	var totalW = 0;

	for (var y in dates) {
		for (var m in dates[y]) {
			var w = dates[y][m].length * this.options.cellWidth;
			totalW = totalW + w;

			monthsDiv.append(
				jQuery('<div>', {
					class: 'ganttview-hzheader-month',
					css: { width: w - 1 + 'px' },
				}).append($.datepicker.regional[$('html').attr('lang')].monthNames[m] + ' ' + y),
			);

			for (var d in dates[y][m]) {
				daysDiv.append(
					jQuery('<div>', { class: 'ganttview-hzheader-day' }).append(dates[y][m][d].getDate()),
				);
			}
		}
	}

	monthsDiv.css('width', totalW + 'px');
	daysDiv.css('width', totalW + 'px');
	headerDiv.append(monthsDiv).append(daysDiv);

	return headerDiv;
};

// Render grid
GanttUtils.prototype.renderGrid = function (dates) {
	var gridDiv = jQuery('<div>', { class: 'ganttview-grid' });
	var rowDiv = jQuery('<div>', { class: 'ganttview-grid-row' });

	for (var y in dates) {
		for (var m in dates[y]) {
			for (var d in dates[y][m]) {
				var cellDiv = jQuery('<div>', { class: 'ganttview-grid-row-cell' });
				if (this.options.showWeekends && this.isWeekend(dates[y][m][d])) {
					cellDiv.addClass('ganttview-weekend');
				}
				if (this.options.showToday && this.isToday(dates[y][m][d])) {
					cellDiv.addClass('ganttview-today');
				}
				rowDiv.append(cellDiv);
			}
		}
	}
	var w = jQuery('div.ganttview-grid-row-cell', rowDiv).length * this.options.cellWidth;
	rowDiv.css('width', w + 'px');
	gridDiv.css('width', w + 'px');

	for (var i = 0; i < this.data.length; i++) {
		gridDiv.append(rowDiv.clone());
	}

	return gridDiv;
};

// Render bar containers
GanttUtils.prototype.addBlockContainers = function () {
	var blocksDiv = jQuery('<div>', { class: 'ganttview-blocks' });

	for (var i = 0; i < this.data.length; i++) {
		blocksDiv.append(jQuery('<div>', { class: 'ganttview-block-container' }));
	}

	return blocksDiv;
};

// Render bars
GanttUtils.prototype.addBlocks = function (slider, start) {
	var rows = jQuery('div.ganttview-blocks div.ganttview-block-container', slider);
	var rowIdx = 0;

	for (var i = 0; i < this.data.length; i++) {
		var series = this.data[i];
		var size = this.daysBetween(series.start, series.end) + 1;
		var offset = this.daysBetween(start, series.start);
		var text = jQuery('<div>', {
			class: 'ganttview-block-text',
			css: {
				width: size * this.options.cellWidth - 19 + 'px',
			},
		});

		var block = jQuery('<div>', {
			class: 'ganttview-block' + (this.options.allowMoves ? ' ganttview-block-movable' : ''),
			css: {
				width: size * this.options.cellWidth - 9 + 'px',
				'margin-left': offset * this.options.cellWidth + 'px',
			},
		}).append(text);

		if (series.type === 'task') {
			this.addTaskBarText(text, series, size);
		}

		block.data('record', series);
		this.setBarColor(block, series);

		jQuery(rows[rowIdx]).append(block);
		rowIdx = rowIdx + 1;
	}
};

GanttUtils.prototype.addTaskBarText = function (container, record, size) {
	if (size >= 4) {
		container.html($('<span>').text(record.progress + ' - #' + record.id + ' ' + record.title));
	} else if (size >= 2) {
		container.html($('<span>').text(record.progress));
	}
};

// Get tooltip for vertical header
GanttUtils.prototype.getVerticalHeaderTooltip = function (record) {
	if (record.type === 'task') {
		return this.getTaskTooltip(record);
	}

	return this.getProjectTooltip(record);
};

GanttUtils.prototype.getTaskTooltip = function (record) {
	var assigneeLabel = $(this.options.container).data('label-assignee');
	var tooltip = $('<span>')
		.append($('<strong>').text(record.column_title + ' (' + record.progress + ')'))
		.append($('<br>'))
		.append($('<span>').text('#' + record.id + ' ' + record.title))
		.append($('<br>'))
		.append($('<span>').text(assigneeLabel + ' ' + (record.assignee ? record.assignee : '')));

	return this.getTooltipFooter(record, tooltip);
};

GanttUtils.prototype.getProjectTooltip = function (record) {
	var tooltip = $('<span>');

	if ('project-manager' in record.users) {
		var projectManagerLabel = $(this.options.container).data('label-project-manager');
		var list = $('<ul>');

		for (var user_id in record.users['project-manager']) {
			list.append($('<li>').append($('<span>').text(record.users['project-manager'][user_id])));
		}

		tooltip.append($('<strong>').text(projectManagerLabel));
		tooltip.append($('<br>'));
		tooltip.append(list);
	}

	return this.getTooltipFooter(record, tooltip);
};

GanttUtils.prototype.getTooltipFooter = function (record, tooltip) {
	var notDefinedLabel = $(this.options.container).data('label-not-defined');
	var startDateLabel = $(this.options.container).data('label-start-date');
	var startEndLabel = $(this.options.container).data('label-end-date');

	if (record.not_defined) {
		tooltip.append($('<br>')).append($('<em>').text(notDefinedLabel));
	} else {
		tooltip.append($('<br>'));
		tooltip.append(
			$('<strong>').text(startDateLabel + ' ' + $.datepicker.formatDate('yy-mm-dd', record.start)),
		);
		tooltip.append($('<br>'));
		tooltip.append(
			$('<strong>').text(startEndLabel + ' ' + $.datepicker.formatDate('yy-mm-dd', record.end)),
		);
	}

	return tooltip;
};

// Set bar color
GanttUtils.prototype.setBarColor = function (block, record) {
	block.css('background-color', record.color.background);
	block.css('border-color', record.color.border);

	if (record.not_defined) {
		if (record.date_started_not_defined) {
			block.css('border-left', '2px solid #000');
		}

		if (record.date_due_not_defined) {
			block.css('border-right', '2px solid #000');
		}
	}

	if (record.progress != '0%') {
		var progressBar = $(block).find('.ganttview-progress-bar');

		if (progressBar.length) {
			progressBar.css('width', record.progress);
		} else {
			block.append(
				jQuery('<div>', {
					class: 'ganttview-progress-bar',
					css: {
						'background-color': record.color.border,
						width: record.progress,
					},
				}),
			);
		}
	}
};

// Setup jquery-ui resizable
GanttUtils.prototype.listenForBlockResize = function (startDate) {
	var self = this;

	jQuery('div.ganttview-block', this.options.container).resizable({
		grid: this.options.cellWidth,
		handles: 'e,w',
		delay: 300,
		stop: function () {
			var block = jQuery(this);
			self.updateDataAndPosition(block, startDate);
			self.saveRecord(block.data('record'));
		},
	});
};

// Setup jquery-ui drag and drop
GanttUtils.prototype.listenForBlockMove = function (startDate) {
	var self = this;

	jQuery('div.ganttview-block', this.options.container).draggable({
		axis: 'x',
		delay: 300,
		grid: [this.options.cellWidth, this.options.cellWidth],
		stop: function () {
			var block = jQuery(this);
			self.updateDataAndPosition(block, startDate);
			self.saveRecord(block.data('record'));
		},
	});
};

// Update the record data and the position on the chart
GanttUtils.prototype.updateDataAndPosition = function (block, startDate) {
	var container = jQuery('div.ganttview-slide-container', this.options.container);
	var scroll = container.scrollLeft();
	var offset = block.offset().left - container.offset().left - 1 + scroll;
	var record = block.data('record');

	// Restore color for defined block
	record.not_defined = false;
	this.setBarColor(block, record);

	// Set new start date
	var daysFromStart = Math.round(offset / this.options.cellWidth);
	var newStart = this.addDays(this.cloneDate(startDate), daysFromStart);
	if (!record.date_started_not_defined || this.compareDate(newStart, record.start)) {
		record.start = this.addDays(this.cloneDate(startDate), daysFromStart);
		record.date_started_not_defined = true;
	} else if (record.date_started_not_defined) {
		delete record.start;
	}

	// Set new end date
	var width = block.outerWidth();
	var numberOfDays = Math.round(width / this.options.cellWidth) - 1;
	var newEnd = this.addDays(this.cloneDate(newStart), numberOfDays);
	if (!record.date_due_not_defined || this.compareDate(newEnd, record.end)) {
		record.end = newEnd;
		record.date_due_not_defined = true;
	} else if (record.date_due_not_defined) {
		delete record.end;
	}

	if (record.type === 'task' && numberOfDays > 0) {
		this.addTaskBarText(jQuery('div.ganttview-block-text', block), record, numberOfDays);
	}

	block.data('record', record);

	// Remove top and left properties to avoid incorrect block positioning,
	// set position to relative to keep blocks relative to scrollbar when scrolling
	block
		.css('top', '')
		.css('left', '')
		.css('position', 'relative')
		.css('margin-left', offset + 'px');
};

// Creates a 3 dimensional array [year][month][day] of every day
// between the given start and end dates
GanttUtils.prototype.getDates = function (start, end) {
	var dates = [];
	dates[start.getFullYear()] = [];
	dates[start.getFullYear()][start.getMonth()] = [start];
	var last = start;

	while (this.compareDate(last, end) == -1) {
		var next = this.addDays(this.cloneDate(last), 1);

		if (!dates[next.getFullYear()]) {
			dates[next.getFullYear()] = [];
		}

		if (!dates[next.getFullYear()][next.getMonth()]) {
			dates[next.getFullYear()][next.getMonth()] = [];
		}

		dates[next.getFullYear()][next.getMonth()].push(next);
		last = next;
	}

	return dates;
};

// Convert data to Date object
GanttUtils.prototype.prepareData = function (data) {
	for (var i = 0; i < data.length; i++) {
		var start = new Date(data[i].start[0], data[i].start[1] - 1, data[i].start[2], 0, 0, 0, 0);
		data[i].start = start;

		var end = new Date(data[i].end[0], data[i].end[1] - 1, data[i].end[2], 0, 0, 0, 0);
		data[i].end = end;
	}

	return data;
};

// Get the start and end date from the data provided
GanttUtils.prototype.getDateRange = function (minDays) {
	var minStart = new Date();
	var maxEnd = new Date();

	for (var i = 0; i < this.data.length; i++) {
		var start = new Date();
		start.setTime(Date.parse(this.data[i].start));

		var end = new Date();
		end.setTime(Date.parse(this.data[i].end));

		if (i == 0) {
			minStart = start;
			maxEnd = end;
		}

		if (this.compareDate(minStart, start) == 1) {
			minStart = start;
		}

		if (this.compareDate(maxEnd, end) == -1) {
			maxEnd = end;
		}
	}

	// Insure that the width of the chart is at least the slide width to avoid empty
	// whitespace to the right of the grid
	if (this.daysBetween(minStart, maxEnd) < minDays) {
		maxEnd = this.addDays(this.cloneDate(minStart), minDays);
	}

	// Always start one day before the minStart
	minStart.setDate(minStart.getDate() - 1);

	return [minStart, maxEnd];
};

// Returns the number of day between 2 dates
GanttUtils.prototype.daysBetween = function (start, end) {
	if (!start || !end) {
		return 0;
	}

	var count = 0,
		date = this.cloneDate(start);

	while (this.compareDate(date, end) == -1) {
		count = count + 1;
		this.addDays(date, 1);
	}

	return count;
};

// Return true if it's the weekend
GanttUtils.prototype.isWeekend = function (date) {
	return date.getDay() % 6 == 0;
};

// Return true if it's today
GanttUtils.prototype.isToday = function (date) {
	var today = new Date();
	return today.toDateString() == date.toDateString();
};

// Clone Date object
GanttUtils.prototype.cloneDate = function (date) {
	return new Date(date.getTime());
};

// Add days to a Date object
GanttUtils.prototype.addDays = function (date, value) {
	date.setDate(date.getDate() + value * 1);
	return date;
};

/**
 * Compares the first date to the second date and returns an number indication of their relative values.
 *
 * -1 = date1 is lessthan date2
 * 0 = values are equal
 * 1 = date1 is greaterthan date2.
 */
GanttUtils.prototype.compareDate = function (date1, date2) {
	if (isNaN(date1) || isNaN(date2)) {
		throw new Error(date1 + ' - ' + date2);
	} else if (date1 instanceof Date && date2 instanceof Date) {
		return date1 < date2 ? -1 : date1 > date2 ? 1 : 0;
	} else {
		throw new TypeError(date1 + ' - ' + date2);
	}
};

KB.on('dom.ready', function () {
	function goToLink(selector) {
		if (!KB.modal.isOpen()) {
			var element = KB.find(selector);

			if (element !== null) {
				window.location = element.attr('href');
			}
		}
	}

	KB.onKey('v+g', function () {
		goToLink('a.view-gantt');
	});

	if (KB.exists('#gantt-chart')) {
		let container = document.getElementById('#gantt-chart');
		let config = container.dataset;
		let utils = new GanttUtils();
		var chart = new Gantt('#gantt-chart', utils.prepareData(config.records), {});
		chart.show();
	}
});
