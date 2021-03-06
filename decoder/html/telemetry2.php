<?php
include "header.inc.php";
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

lastrxtime = <?=time()-$range?>;
range = <?=$range?>;
call = '<?=$_GET['call']?>';
var last = null;
var lastChartUpdate = 0;

const COL_GREEN		= "#008000";
const COL_ORANGE	= "#CC6600";
const COL_RED		= "#FF0000";

function number_format(number, decimals, decPoint, thousandsSep) { // eslint-disable-line camelcase
	//  discuss at: http://locutus.io/php/number_format/
	// original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// improved by: Kevin van Zonneveld (http://kvz.io)
	// improved by: davook
	// improved by: Brett Zamir (http://brett-zamir.me)
	// improved by: Brett Zamir (http://brett-zamir.me)
	// improved by: Theriault (https://github.com/Theriault)
	// improved by: Kevin van Zonneveld (http://kvz.io)
	// bugfixed by: Michael White (http://getsprink.com)
	// bugfixed by: Benjamin Lupton
	// bugfixed by: Allan Jensen (http://www.winternet.no)
	// bugfixed by: Howard Yeend
	// bugfixed by: Diogo Resende
	// bugfixed by: Rival
	// bugfixed by: Brett Zamir (http://brett-zamir.me)
	//  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	//  revised by: Luke Smith (http://lucassmith.name)
	//    input by: Kheang Hok Chin (http://www.distantia.ca/)
	//    input by: Jay Klehr
	//    input by: Amir Habibi (http://www.residence-mixte.com/)
	//    input by: Amirouche
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number;
	var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
	var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep;
	var dec = (typeof decPoint === 'undefined') ? '.' : decPoint;
	var s = '';
	var toFixedFix = function (n, prec) {
		var k = Math.pow(10, prec);
		return '' + (Math.round(n * k) / k).toFixed(prec);
	}
	// @todo: for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if(s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}

function colorize(color, str) {
	return "<font color=\"" + color + "\">" + str + "</font>";
}

function time_format(time) {
	if(time == undefined)
		return "never";

	if(time < 3600)
		return Math.floor(time/60) + "m" + (time%60) + "s ago";
	else
		return Math.floor(time/3600) + "h" + Math.floor((time/60)%60) + "m ago";
}

function get_alt(p) {
	return Math.round((Math.pow(parseInt(p)/101325, -5.2561)-1) / 0.0068755856);
}

function loadRecentData() {

	$.getJSON("ajax/telemetry.php?call=" + call + "&from=" + lastrxtime, function(json) {
		tel = json['telemetry'];
		if(tel.length) {
			lastrxtime = tel[tel.length-1].rxtime+1;

			$.each(tel[tel.length-1], function(key, d) {
				switch(key) {

					case 'sen_i1_press':
						$('#sen_i1_press').text(number_format(d/10) + 'Pa');
						$('#sen_i1_alt').text(number_format(get_alt(d/10)) + 'm');
						break;

					case 'sen_e1_press':
						$('#sen_e1_press').text(number_format(d/10) + 'Pa');
						$('#sen_e1_alt').text(number_format(get_alt(d/10)) + 'm');
						break;

					case 'sen_e2_press':
						$('#sen_e2_press').text(number_format(d/10) + 'Pa');
						$('#sen_e2_alt').text(number_format(get_alt(d/10)) + 'm');
						break;

					case 'sen_i1_hum':
					case 'sen_e1_hum':
					case 'sen_e2_hum':
						$('#' + key).text(number_format(d) + '%');
						break;

					case 'sen_i1_temp':
					case 'sen_e1_temp':
					case 'sen_e2_temp':
					case 'stm32_temp':
					case 'si4464_temp':
						$('#' + key).text(number_format(d/100, 2) + '°C');
						break;

					case 'gps_pdop':
						$('#' + key).text(number_format(d/20, 2));
						break;

					case 'gps_lat':
						s  = d < 0 ? "S" : "N";
						s += Math.abs(d) < 100000000 ? "0" : "";
						s += number_format(Math.abs(d)/10000000, 5);
						$('#' + key).text(s);
						break;

					case 'gps_lon':
						s  = d < 0 ? "W" : "E";
						s += Math.abs(d) < 100000000 ? "0" : "";
						s += Math.abs(d) < 1000000000 ? "0" : "";
						s += number_format(Math.abs(d)/10000000, 5);
						$('#' + key).text(s);
						break;

					case 'gps_lock':
						switch(d) {
							case 0: $('#' + key).html(colorize(COL_GREEN, "GPS locked")); break;
							case 1: $('#' + key).html(colorize(COL_GREEN, "GPS locked - kept switched on")); break;
							case 2: $('#' + key).html(colorize(COL_RED, "GPS loss")); break;
							case 3: $('#' + key).html(colorize(COL_ORANGE, "Low Batt before switched on")); break;
							case 4: $('#' + key).html(colorize(COL_ORANGE, "Low Batt while switched on")); break;
							case 5: $('#' + key).html(colorize(COL_GREEN, "Data from memory")); break;
							case 6: $('#' + key).html(colorize(COL_RED, "GPS never locked")); break;
							case 7: $('#' + key).html(colorize(COL_RED, "GPS communication error")); break;
							case 8: $('#' + key).html(colorize(COL_ORANGE, "GPS not used - fixed loc set")); break;
						}
						break;

					case 'err_ov5640':
						switch(d) {
							case 0: $('#' + key).html(colorize(COL_GREEN, "OK")); break;
							case 1: $('#' + key).html(colorize(COL_RED, "I2C Error - Camera not found")); break;
							case 2: $('#' + key).html(colorize(COL_RED, "DMA abort - last buffer segment")); break;
							case 3: $('#' + key).html(colorize(COL_RED, "DMA FIFO error")); break;
							case 4: $('#' + key).html(colorize(COL_RED, "DMA stream transfer error")); break;
							case 5: $('#' + key).html(colorize(COL_RED, "DMA direct mode error")); break;
						}
						break;

					case 'err_pac1720':
						switch(d) {
							case 0: $('#' + key).html(colorize(COL_GREEN, "OK")); break;
							case 1:
							case 3: $('#' + key).html(colorize(COL_RED, "Fail")); break;
							case 2: $('#' + key).html(colorize(COL_RED, "Unreliable values")); break;
						}
						break;

					case 'err_i2c1':
					case 'err_i2c2':
					case 'err_eva7m':
						switch(d) {
							case 0: $('#' + key).html(colorize(COL_GREEN, "OK")); break;
							case 1: $('#' + key).html(colorize(COL_RED, "Fail")); break;
						}
						break;

					case 'err_bme280_i1':
					case 'err_bme280_e1':
					case 'err_bme280_e2':
						var sen = key.substr(-2, 2);
						switch(d) {
							case 1:
								$('#sen_' + sen + '_press').html(colorize(COL_RED, "Fail"));
								$('#sen_' + sen + '_temp').text('');
								$('#sen_' + sen + '_hum').text('');
								$('#sen_' + sen + '_alt').text('');
							case 2:
								$('#sen_' + sen + '_press').html(colorize(COL_ORANGE, "Not fitted"));
								$('#sen_' + sen + '_temp').text('');
								$('#sen_' + sen + '_hum').text('');
								$('#sen_' + sen + '_alt').text('');
								break;
						}
						break;

					case 'rxtime':
					case 'gps_time':
						var a = new Date(d * 1000);
						var year = a.getFullYear();
						var month = a.getMonth() < 9 ? "0" + (a.getMonth()+1) : (a.getMonth()+1);
						var date = a.getDate() < 10 ? "0" + a.getDate() : a.getDate();
						var hour = a.getHours() < 10 ? "0" + a.getHours() : a.getHours();
						var min = a.getMinutes() < 10 ? "0" + a.getMinutes() : a.getMinutes();
						var sec = a.getSeconds() < 10 ? "0" + a.getSeconds() : a.getSeconds();
						$('#' + key).text(year + '-' + month + '-' + date + ' ' + hour + ':' + min + ':' + sec);
						break;

					case 'pac_pbat':
					case 'pac_psol':
						$('#' + key).text(number_format(d/10, 1));
						break;

					case 'sys_time':
						$('#' + key).text(number_format(d) + 's');
						break;

					case 'gps_alt':
						$('#' + key).text(number_format(d) + 'm');
						break;

					default:
						if(Number.isInteger(d)) {
							$('#' + key).text(number_format(d));
						} else {
							$('#' + key).text(d);
						}
				}
			});
		}

		$('#pos_cnt300').text(json['packetCount']['pos']['cnt300']);
		$('#pos_cnt3600').text(json['packetCount']['pos']['cnt3600']);
		$('#pos_cnt86400').text(json['packetCount']['pos']['cnt86400']);

		$('#dir_cnt300').text(json['packetCount']['dir']['cnt300']);
		$('#dir_cnt3600').text(json['packetCount']['dir']['cnt3600']);
		$('#dir_cnt86400').text(json['packetCount']['dir']['cnt86400']);

		$('#img_cnt300').text(json['packetCount']['img']['cnt300']);
		$('#img_cnt3600').text(json['packetCount']['img']['cnt3600']);
		$('#img_cnt86400').text(json['packetCount']['img']['cnt86400']);

		$('#log_cnt300').text(json['packetCount']['log']['cnt300']);
		$('#log_cnt3600').text(json['packetCount']['log']['cnt3600']);
		$('#log_cnt86400').text(json['packetCount']['log']['cnt86400']);

		$('#act_pos').text(time_format(json['lastActivity']['pos']));
		$('#act_img').text(time_format(json['lastActivity']['img']));
		$('#act_log').text(time_format(json['lastActivity']['log']));
		$('#act_dir').text(time_format(json['lastActivity']['dir']));

		// Update charts if there is new data or at a timeout of 300 seconds
		if(tel.length > 0 || json['time']-lastChartUpdate > 300) {

			// Add new rows
			var time = json['time'];
			xAxis.minValue = new Date((time-<?=$range?>)*1000),
			xAxis.maxValue = new Date(time*1000),
			xAxis.ticks = [];
			interval = <?=$range?> / 48;
			for(i=0; i<=48; i++)
				xAxis.ticks.push(new Date(((Math.floor(time/interval)*interval)-i*interval)*1000))

			$.each(tel, function(key, data) {

				var time = new Date(data['org'] == 'pos' ? data['rxtime']*1000 : data['gps_time']*1000);

				if(last != null && time - last > range*1000/60) { // Gap in the data set
					dataBattery.addRow([null, null, null, null]);
					dataSolar.addRow([null, null, null, null]);
					dataTemp.addRow([null,null,null,null,null,null]);
					dataGPS.addRow([null,null,null,null]);
					dataLight.addRow([null,null]);
					dataAlt.addRow([null,null,null,null,null]);
				}


				dataBattery.addRow([time, data['adc_vbat'], data['pac_vbat'], data['pac_pbat']/10]);
				dataSolar.addRow([time, data['adc_vsol'], data['pac_vsol'], data['pac_psol']/10]);
				dataTemp.addRow([
					time,
					data['sen_i1_temp'] && data['sen_i1_temp'] > -10000 && data['sen_i1_temp'] < 10000 ? data['sen_i1_temp']/100 : null,
					data['sen_e1_temp'] && data['sen_e1_temp'] > -10000 && data['sen_e1_temp'] < 10000 ? data['sen_e1_temp']/100 : null,
					data['sen_e2_temp'] && data['sen_e2_temp'] > -10000 && data['sen_e2_temp'] < 10000 ? data['sen_e2_temp']/100 : null,
					data['stm32_temp'] && data['stm32_temp'] > -10000 && data['stm32_temp'] < 10000 ? data['stm32_temp']/100 : null,
					data['si4464_temp'] && data['si4464_temp'] > -10000 && data['si4464_temp'] < 10000 ? data['si4464_temp']/100 : null
				]);
				dataGPS.addRow([time, data['gps_sats'], data['gps_ttff'], data['gps_pdop']/20]);
				dataLight.addRow([time, data['light_intensity']]);
				dataAlt.addRow([
					time,
					data['gps_alt'],
					data['sen_i1_press'] && data['sen_i1_press'] < 1100000 ? data['sen_i1_press']/10 : null,
					data['sen_e1_press'] && data['sen_e1_press'] < 1100000 ? data['sen_e1_press']/10 : null,
					data['sen_e2_press'] && data['sen_e2_press'] < 1100000 ? data['sen_e2_press']/10 : null
				]);

				last = time;
			});

			// Remove old rows
			var removeTime = new Date((time-<?=$range?>)*1000);
			for(var c=0; c<dataBattery.getNumberOfColumns(); c++) {
				if(dataBattery.getValue(c, 0) < removeTime) {
					dataBattery.removeRow(c);
					dataSolar.removeRow(c);
					dataTemp.removeRow(c);
					dataGPS.removeRow(c);
					dataLight.removeRow(c);
					dataAlt.removeRow(c);
				}
			}

			// Update charts
			batteryChart.draw(dataBattery, voltageOptions);
			solarChart.draw(dataSolar, voltageOptions);
			tempChart.draw(dataTemp, tempOptions);
			gpsChart.draw(dataGPS, gpsOptions);
			lightChart.draw(dataLight, lightOptions);
			altChart.draw(dataAlt, altOptions);

			lastChartUpdate = json['time'];
		}

	});
}

// Common
var xAxis = {
	format:<?=($range >= 86400 ? "'d. H:m'" : "'H:m'")?>,
	slantedTextAngle: 90
}
var area = {'width': '80%', top: 20, bottom: 70};
var scroll = {axis: 'horizontal', keepInBounds: true, maxZoomIn: 20.0};

// Chart 1/2
var batteryChart;
var solarChart;
var dataBattery;
var dataSolar;
var voltageOptions = {
	//explorer: scroll,
	height: 250,
	series: {
		0: {targetAxisIndex: 0},
		1: {targetAxisIndex: 0},
		2: {targetAxisIndex: 1}
	},
	vAxes: {
		0: {title: 'Voltage (mV)'},
		1: {title: 'Power (mW)'},
	},
	legend: {
		position: 'top'
	},
	hAxis: xAxis,

	chartArea: area
};

// Chart 3
var tempChart;
var dataTemp;
var tempOptions = {
	//explorer: scroll,
	height: 250,
	series: {
		0: {targetAxisIndex: 0},
		1: {targetAxisIndex: 0},
		2: {targetAxisIndex: 0},
		3: {targetAxisIndex: 0},
		4: {targetAxisIndex: 0}
	},
	vAxis: {
		title: 'Temp (°C)',
	},
	legend: {
		position: 'top'
	},
	hAxis: xAxis,
	chartArea: area
};

// Chart 4
var gpsChart;
var dataGPS;
var gpsOptions = {
	//explorer: scroll,
	height: 250,
	series: {
		0: {targetAxisIndex: 0},
		1: {targetAxisIndex: 1},
		2: {targetAxisIndex: 0}
	},
	vAxes: {
		0: {title: 'Sats / pDOP'},
		1: {title: 'TTFF'},
	},
	legend: {
		position: 'top'
	},
	hAxis: xAxis,
	chartArea: area
};

// Chart 5
var altChart;
var dataAlt;
var altOptions = {
	//explorer: scroll,
	height: 250,
	series: {
		0: {targetAxisIndex: 0},
		1: {targetAxisIndex: 1},
		2: {targetAxisIndex: 1},
		3: {targetAxisIndex: 1}
	},
	vAxes: {
		0: {title: 'Altitude'},
		1: {title: 'Airpressure'},
	},
	legend: {
		position: 'top'
	},
	hAxis: xAxis,
	chartArea: area
};

// Chart 6
var lightChart;
var dataLight;
var lightOptions = {
	//explorer: scroll,
	height: 250,
	series: {
		0: {targetAxisIndex: 0}
	},
	legend: {
		position: 'top'
	},
	hAxis: xAxis,
	chartArea: area
};

google.charts.load('current', {'packages':['line', 'corechart']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
	// Chart 1
	dataBattery = new google.visualization.DataTable();
	dataBattery.addColumn('date', 'Time');
	dataBattery.addColumn('number', "VBAT_STM");
	dataBattery.addColumn('number', "VBAT_PAC");
	dataBattery.addColumn('number', "PBAT_PAC");
	batteryChart = new google.visualization.LineChart(document.getElementById('batteryDiv'));

	// Chart 2
	dataSolar = new google.visualization.DataTable();
	dataSolar.addColumn('date', 'Time');
	dataSolar.addColumn('number', "VSOL_STM");
	dataSolar.addColumn('number', "VSOL_PAC");
	dataSolar.addColumn('number', "PSOL_PAC");
	solarChart = new google.visualization.LineChart(document.getElementById('solarDiv'));

	// Chart 3
	dataTemp = new google.visualization.DataTable();
	dataTemp.addColumn('date', 'Time');
	dataTemp.addColumn('number', "TEMP_BME_I1");
	dataTemp.addColumn('number', "TEMP_BME_E1");
	dataTemp.addColumn('number', "TEMP_BME_E2");
	dataTemp.addColumn('number', "TEMP_STM32");
	dataTemp.addColumn('number', "TEMP_Si4464");
	tempChart = new google.visualization.LineChart(document.getElementById('tempDiv'));

	// Chart 4
	dataGPS = new google.visualization.DataTable();
	dataGPS.addColumn('date', 'Time');
	dataGPS.addColumn('number', "Sats");
	dataGPS.addColumn('number', "TTFF");
	dataGPS.addColumn('number', "pDOP");
	gpsChart = new google.visualization.LineChart(document.getElementById('gpsDiv'));

	// Chart 5
	dataAlt = new google.visualization.DataTable();
	dataAlt.addColumn('date', 'Time');
	dataAlt.addColumn('number', "GPS_ALT");
	dataAlt.addColumn('number', "PRESS_BME_I1");
	dataAlt.addColumn('number', "PRESS_BME_E1");
	dataAlt.addColumn('number', "PRESS_BME_E2");
	altChart = new google.visualization.LineChart(document.getElementById('altDiv'));

	// Chart 6
	dataLight = new google.visualization.DataTable();
	dataLight.addColumn('date', 'Time');
	dataLight.addColumn('number', "LIGHT");
	lightChart = new google.visualization.LineChart(document.getElementById('lightDiv'));

	loadRecentData();
	setInterval(loadRecentData, 1000);
}
</script>
</head>
<body>

<?php
include "sidebar.inc.php";
?>


<div class="wrapper">
	<telemetry class="inner telemetry">
		<div style="width:168px;">Call<br><span id="call" class="fat"></span></div>
		<div style="width:120px;">Reset<br><span id="reset" class="fat"></span></div>
		<div style="width:120px;">ID<br><span id="id" class="fat"></span></div>
		<div style="width:250px;">Time (RX)<br><span id="rxtime" class="fat"></span></div>
		<div style="width:174px;">Time (SYS)<br><span id="sys_time" class="fat"></span></div>

		<div style="width:440px;">
			Packets<br>
			<table>
				<tr>
					<td width="285"></td>
					<td width="100">Last Packet</td>
					<td>in 24h</td>
				</tr>
				<tr>
					<td><b>BCN</b> ______________________________</td><td><span id="act_pos"></span></td><td><span id="pos_cnt86400"></span></b></td>
				</tr>
				<tr>
					<td><b>DIR</b> ______________________________</td><td><span id="act_dir"></span></td><td><span id="dir_cnt86400"></span></b></td>
				</tr>
				<tr>
					<td><b>IMG</b> ______________________________</td><td><span id="act_img"></span></td><td><span id="img_cnt86400"></span></b></td>
				</tr>
				<tr>
					<td><b>LOG</b> ______________________________</td><td><span id="act_log"></span></td><td><span id="log_cnt86400"></span></b></td>
				</tr>
				<tr>
					<td colspan="4"><b>T-</b> &nbsp;30&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;20&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;10&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0 min ago</td>
				</tr>
			</table>
		</div>
	</telemetry>


	<telemetry class="inner telemetry">
		<div style="width:440px;height:108px;background:url(power.png) no-repeat #ccdaec;">
			<div style="margin-left:107px;margin-top:10px;width:70px;text-align:center;"><span id="pac_psol"></span>mW</div>
			<div style="margin-left:80px;margin-top:10px;width:70px;text-align:center;"><span id="pac_pbat"></span>mW</div><br>

			<div style="margin-left:0px;margin-top:25px;width:90px;text-align:center;">
				<span id="adc_vsol"></span>mV<sub>STM</sub><br>
				<span id="pac_vsol"></span>mV<sub>PAC</sub>
			</div>
			<div style="margin-left:85px;margin-top:25px;width:90px;text-align:center;">
				<span id="adc_vbat"></span>mV<sub>STM</sub><br>
				<span id="pac_vbat"></span>mV<sub>PAC</sub>
			</div>
			<div style="margin-left:95px;margin-top:45px;">Power</div>
		</div>
		<div style="width:440px;height:123px;background:url(gnss.png) no-repeat #ccdaec;">
			<div style="margin-left:75px;">
				<b><span id="gps_lock" class="fat"></span></b>
				<p><span id="gps_lat" class="fat"></span> <span id="gps_lon" class="fat"></span> <img src="arrow_up.png"><span id="gps_alt" class="fat"></span></p>
				<span id="gps_sats"></span> Sats, TTFF <span id="gps_ttff"></span>s, pDOP <span id="gps_pdop"></span><br>
				Time: <span id="gps_time"></span>
			</div>
		</div>
	</telemetry>

	<telemetry class="inner telemetry">
		<div style="width:440px;height:108px;">
			Sensors:
			<table>
				<tr>
					<td width="80"></td>
					<td width="100">Airpressure</td>
					<td width="70">Temp.</td>
					<td width="70">Hum.</td>
					<td width="70">FL</td>
				</tr>
				<tr>
					<td><b>BME280<sub>I1</sub></b></td>
					<td><span id="sen_i1_press"></span></td>
					<td><span id="sen_i1_temp"></span></td>
					<td><span id="sen_i1_hum"></span></td>
					<td><span id="sen_i1_alt"></span></td>
				</tr>
				<tr>
					<td><b>BME280<sub>E1</sub></b></td>
					<td><span id="sen_e1_press"></span></td>
					<td><span id="sen_e1_temp"></span></td>
					<td><span id="sen_e1_hum"></span></td>
					<td><span id="sen_e1_alt"></span></td>
				</tr>
				<tr>
					<td><b>BME280<sub>E2</sub></b></td>
					<td><span id="sen_e2_press"></span></td>
					<td><span id="sen_e2_temp"></span></td>
					<td><span id="sen_e2_hum"></span></td>
					<td><span id="sen_e2_alt"></span></td>
				</tr>
			</table>
			<!--<table>
				<tr>
					<th>Temp.:</th>
					<td></td>
					<td><span id="stm32_temp"></span>°C<sub>STM</sub>, <span id="si4464_temp"></span>°C<sub>Si4464</sub></td>
				</tr>
				<tr>
					<th>Light</th>
					<td></td>
					<td><span id="light_intensity"></span><sub>OV5640</sub></td>
				</tr>
			</table>-->
		</div>
		<div style="width:440px;height:123px;">

		</div>
	</telemetry>

		<!--<table>
			<tr>
				<th width="70">Battery:</th>
				<td></td>
				<td>, <span id="pac_pbat"></span>mW<sub>PAC</sub></td>
			</tr>
			<tr>
				<th>Solar:</th>
				<td></td>
				<td><span id="adc_vsol"></span>mV<sub>STM</sub>, <span id="pac_vsol"></span>mV<sub>PAC</sub>, <span id="pac_psol"></span>mW<sub>PAC</sub></td>
			</tr>
			<tr height="5"></tr>
			<tr>
				<th>GPS:</th>
				<td></td>
				<td>
					<b><span id="gps_lock"></span></b><br>
					<span id="gps_sats"></span> Sats, TTFF <span id="gps_ttff"></span>s, pDOP <span id="gps_pdop"></span><br>
					Time: <span id="gps_time"></span><br>
					<span id="gps_lat"></span> <span id="gps_lon"></span> <span id="gps_alt"></span>m
				</td>
			</tr>
			<tr height="5"></tr>
			<tr>
				<th>Sensors:</th>
				<td width="75">BME280<sub>I1</sub>:</td>
				<td><span id="sen_i1_press"></span>Pa, <span id="sen_i1_temp"></span>°C, <span id="sen_i1_hum"></span>%</td>
			</tr>
			<tr>
				<td></td>
				<td>BME280<sub>E1</sub>:</td>
				<td><span id="sen_e1_press"></span>Pa, <span id="sen_e1_temp"></span>°C, <span id="sen_e1_hum"></span>%</td>
			</tr>
			<tr>
				<td></td>
				<td>BME280<sub>E2</sub>:</td>
				<td><span id="sen_e2_press"></span>Pa, <span id="sen_e2_temp"></span>°C, <span id="sen_e2_hum"></span>%</td>
			</tr>
			<tr>
				<th>Temp.:</th>
				<td></td>
				<td><span id="stm32_temp"></span>°C<sub>STM</sub>, <span id="si4464_temp"></span>°C<sub>Si4464</sub></td>
			</tr>
			<tr>
				<th>Light</th>
				<td></td>
				<td><span id="light_intensity"></span><sub>OV5640</sub></td>
			</tr>
		</table>-->



	<!--<telemetry class="inner telemetry" style="width:420px;height:260px;">
		<table>
			<tr>
				<td width="50%">
					<table>
						<tr>
							<th width="75">I2C<sub>INT</sub></th>
							<td><b><span id="err_i2c1"></span></b></td>
						</tr>
						<tr>
							<th>I2C<sub>EXT</sub></th>
							<td><b><span id="err_i2c2"></span></b></td>
						</tr>
						<tr height="5"></tr>
						<tr>
							<th>EVA7M:</th>
							<td width="75"><b><span id="err_eva7m"></span></b></td>
						</tr>
						<tr>
							<th>PAC1720:</th>
							<td width="230"><b><span id="err_pac1720"></span></b></td>
						</tr>
						<tr>
							<th>OV5640:</th>
							<td><b><span id="err_ov5640"></span></b></td>
						</tr>
					</table>
				</td>
				<td width="50%">
					<table>
						<tr>
							<th width="75">BME280<sub>I1</sub>:</th>
							<td><b><span id="err_bme280_i1"></span></b></td>
						</tr>
						<tr>

							<th>BME280<sub>E1</sub>:</th>
							<td><b><span id="err_bme280_e1"></span></b></td>
						</tr>
						<tr>
							<th>BME280<sub>E2</sub>:</th>
							<td><b><span id="err_bme280_e2"></span></b></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</telemetry>-->


	<div class="range">
		&nbsp;Range:
		<a href="?call=<?=$_GET['call']?>&range=3600">1h</a>
		<a href="?call=<?=$_GET['call']?>&range=10800">3h</a>
		<a href="?call=<?=$_GET['call']?>&range=21600">6h</a>
		<a href="?call=<?=$_GET['call']?>&range=43200">12h</a>
		<a href="?call=<?=$_GET['call']?>&range=86400">24h</a>
		<a href="?call=<?=$_GET['call']?>&range=172800">2d</a>
		<a href="?call=<?=$_GET['call']?>&range=259200">3d</a>
		<a href="?call=<?=$_GET['call']?>&range=432000">5d</a>
		<a href="?call=<?=$_GET['call']?>&range=604800">7d</a>
		<a href="?call=<?=$_GET['call']?>&range=1209600">14d</a>
		<a href="?call=<?=$_GET['call']?>&range=1814400">21d</a>
		<a href="?call=<?=$_GET['call']?>&range=2592000">30d</a>
	</div>

	<div id="batteryDiv" class="inner chart"></div>
	<div id="solarDiv" class="inner chart"></div>
	<div id="tempDiv" class="inner chart"></div>
	<div id="gpsDiv" class="inner chart"></div>
	<div id="altDiv" class="inner chart"></div>
	<div id="lightDiv" class="inner chart"></div>
</div>

</body>
</html>
<?php
include "footer.inc.php";
?>

