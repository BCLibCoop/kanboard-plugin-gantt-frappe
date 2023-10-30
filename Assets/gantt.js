// Based on jQuery.ganttView v.0.8.8 Copyright (c) 2010 JC Grubbs - jc.grubbs@devmynd.com - MIT License

KB.on('dom.ready', function () {
	const links = {};
	const GanttUtils = {
		saveRecord: (record) => {
			$.ajax({
				cache: false,
				url: window.ganttSaveController,
				contentType: 'application/json',
				type: 'POST',
				processData: false,
				data: JSON.stringify(record),
			});
		},
		onClick: function (task, event, container) {
			if (typeof links['t' + task.id] === 'undefined') {
				const dropdown = document.getElementById('dropdown-task-id-' + task.id);

				if (dropdown === null) {
					return;
				}

				const bar = container.querySelector('[data-id="' + task.id + '"]');
				links['t' + task.id] = dropdown.querySelector('a.dropdown-menu-link-icon');

				if (!dropdown.dataset.mounted) {
					const label = bar.querySelector('.bar-label');
					label.appendChild(dropdown);
					dropdown.dataset.mounted = true;
				}
			}

			if (links['t' + task.id] != null) {
				links['t' + task.id].click();
			}

			const dropdownContainer = document.getElementById('dropdown');

			if (dropdownContainer != null) {
				const ul = dropdownContainer.querySelector('.dropdown-submenu-open');
				ul.style.top = event.pageY + 'px';
				ul.style.left = event.pageX + 'px';
			}
		},
		onDateChange: (task, start, end) => {
			task.date_started = start;
			task.date_due = end;
			GanttUtils.saveRecord(task);
		},
		goToLink: (selector) => {
			if (!KB.modal.isOpen()) {
				const element = KB.find(selector);

				if (element !== null) {
					window.location = element.attr('href');
				}
			}
		}
	};

	KB.onKey('v+g', function () {
		GanttUtils.goToLink('a.view-gantt');
	});

	if (KB.exists('#gantt-chart')) {
		const container = document.getElementById('gantt-chart');

		const gantt = new Gantt('#gantt-chart', window.ganttTasks ?? [], {
			bar_height: 30,
			view_modes: ['Half Day', 'Day', 'Week', 'Month', 'Quarter'],
			view_mode: 'Day',
			popup_trigger: 'mouseover',
			on_click: function (task, event) {
				GanttUtils.onClick(task, event, container);
			},
			on_date_change: function (task, start, end) {
				GanttUtils.onDateChange(task, start, end);
			},
		});

		// Use ID for kanboard styling
		gantt.popup_wrapper.id = 'tooltip-container';

		$('.plugin-header .views-switcher-component a').on('click', function (e) {
			e.preventDefault();
			const $btn = $(this);

			gantt.change_view_mode($btn.text());
			$btn.parent().parent().find('li').removeClass('active');
			$btn.parent('li').addClass('active');
		});
	}
});
