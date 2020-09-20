
function buildChart(element) {
	let $e = $(element);
	const data = $e.data('chart-data');

	if (!data) {
		return;
	}

	const targetId = $e.data('chart-target');
	const target = document.getElementById(targetId);

	let table = new google.visualization.DataTable();

	data.series.forEach(el => {
		table.addColumn(el.type, el.title);
	});

	table.addRows(data.rows);

	let chart;

	if (data.type == 'column') {
		chart = new google.visualization.ColumnChart(target);
	} else if (data.type == 'line') {
		chart = new google.visualization.LineChart(target);
	} else {
		console.error('Uknown chart type \'' + data.type + '\'');
		return;
	}

	chart.draw(table, data.options);
}

export default ($context) => {
	let items = $context.find('[data-chart-data]');

	if (!items.length) {
		return;
	}

	google.charts.load('current', { 'packages': ['corechart'], 'language': 'cs' });

	google.charts.setOnLoadCallback(() => {
		items.each((i, e) => {
			buildChart(e);
		});
	});
}