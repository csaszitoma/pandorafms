<?php

// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2009 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list

// This program is free software; you can redistribute it and/or
// modify it under the terms of the  GNU Lesser General Public License
// as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

/**
 * @package Include
 * @subpackage Reporting
 */

/**
 * Include the usual functions
 */
require_once ($config["homedir"]."/include/functions.php");
require_once ($config["homedir"]."/include/functions_db.php");
require_once ($config["homedir"]."/include/functions_agents.php");


/** 
 * Get the average value of an agent module in a period of time.
 * 
 * @param int Agent module id
 * @param int Period of time to check (in seconds)
 * @param int Top date to check the values. Default current time.
 * 
 * @return float The average module value in the interval.
 */
function get_agentmodule_data_average ($id_agent_module, $period, $date = 0) {
	global $config;
	
	// Initialize variables
	if (empty ($date)) $date = get_system_time ();
	if ((empty ($period)) OR ($period == 0)) $period = $config["sla_period"];
	$datelimit = $date - $period;	

	// Get module data
	$interval_data = get_db_all_rows_sql ('SELECT * FROM tagente_datos 
			WHERE id_agente_modulo = ' . (int) $id_agent_module .
			' AND utimestamp > ' . (int) $datelimit .
			' AND utimestamp < ' . (int) $date .
			' ORDER BY utimestamp ASC', true);
	if ($interval_data === false) $interval_data = array ();

	// Get previous data
	$previous_data = get_previous_data ($id_agent_module, $datelimit);
	if ($previous_data !== false) {
		$previous_data['utimestamp'] = $datelimit;
		array_unshift ($interval_data, $previous_data);
	}

	// Get next data
	$next_data = get_next_data ($id_agent_module, $date);
	if ($next_data !== false) {
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	} else if (count ($interval_data) > 0) {
		// Propagate the last known data to the end of the interval
		$next_data = array_pop ($interval_data);
		array_push ($interval_data, $next_data);
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	}

	if (count ($interval_data) < 2) {
		return false;
	}

	// Set initial conditions
	$total = 0;
	$previous_data = array_shift ($interval_data);
	foreach ($interval_data as $data) {
		$total += $previous_data['datos'] * ($data['utimestamp'] -  $previous_data['utimestamp']);
		$previous_data = $data;
	}

	if ($period == 0) {
		return 0;
	}

	return $total / $period;
}

/** 
 * Get the maximum value of an agent module in a period of time.
 * 
 * @param int Agent module id to get the maximum value.
 * @param int Period of time to check (in seconds)
 * @param int Top date to check the values. Default current time.
 * 
 * @return float The maximum module value in the interval.
 */
function get_agentmodule_data_max ($id_agent_module, $period, $date = 0) {
	global $config;
	
	// Initialize variables
	if (empty ($date)) $date = get_system_time ();
	if ((empty ($period)) OR ($period == 0)) $period = $config["sla_period"];
	$datelimit = $date - $period;	

	// Get module data
	$interval_data = get_db_all_rows_sql ('SELECT * FROM tagente_datos 
			WHERE id_agente_modulo = ' . (int) $id_agent_module .
			' AND utimestamp > ' . (int) $datelimit .
			' AND utimestamp < ' . (int) $date .
			' ORDER BY utimestamp ASC', true);
	if ($interval_data === false) $interval_data = array ();

	// Get previous data
	$previous_data = get_previous_data ($id_agent_module, $datelimit);
	if ($previous_data !== false) {
		$previous_data['utimestamp'] = $datelimit;
		array_unshift ($interval_data, $previous_data);
	}

	// Get next data
	$next_data = get_next_data ($id_agent_module, $date);
	if ($next_data !== false) {
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	} else if (count ($interval_data) > 0) {
		// Propagate the last known data to the end of the interval
		$next_data = array_pop ($interval_data);
		array_push ($interval_data, $next_data);
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	}

	if (count ($interval_data) < 2) {
		return false;
	}

	// Set initial conditions
	$previous_data = array_shift ($interval_data);
	if ($previous_data['utimestamp'] == $datelimit) {
		$max = $previous_data['datos'];
	} else {
		$max = 0;
	}
	foreach ($interval_data as $data) {
		if ($data['datos'] > $max) {
			$max = $data['datos'];
		}
	}

	return $max;
}

/** 
 * Get the minimum value of an agent module in a period of time.
 * 
 * @param int Agent module id to get the minimum value.
 * @param int Period of time to check (in seconds)
 * @param int Top date to check the values in Unix time. Default current time.
 * 
 * @return float The minimum module value of the module
 */
function get_agentmodule_data_min ($id_agent_module, $period, $date = 0) {
	global $config;
	
	// Initialize variables
	if (empty ($date)) $date = get_system_time ();
	if ((empty ($period)) OR ($period == 0)) $period = $config["sla_period"];
	$datelimit = $date - $period;	

	// Get module data
	$interval_data = get_db_all_rows_sql ('SELECT * FROM tagente_datos 
			WHERE id_agente_modulo = ' . (int) $id_agent_module .
			' AND utimestamp > ' . (int) $datelimit .
			' AND utimestamp < ' . (int) $date .
			' ORDER BY utimestamp ASC', true);
	if ($interval_data === false) $interval_data = array ();

	// Get previous data
	$previous_data = get_previous_data ($id_agent_module, $datelimit);
	if ($previous_data !== false) {
		$previous_data['utimestamp'] = $datelimit;
		array_unshift ($interval_data, $previous_data);
	}

	// Get next data
	$next_data = get_next_data ($id_agent_module, $date);
	if ($next_data !== false) {
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	} else if (count ($interval_data) > 0) {
		// Propagate the last known data to the end of the interval
		$next_data = array_pop ($interval_data);
		array_push ($interval_data, $next_data);
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	}

	if (count ($interval_data) < 2) {
		return false;
	}

	// Set initial conditions
	$previous_data = array_shift ($interval_data);
	if ($previous_data['utimestamp'] == $datelimit) {
		$min = $previous_data['datos'];
	} else {
		$min = 0;
	}
	foreach ($interval_data as $data) {
		if ($data['datos'] < $min) {
			$min = $data['datos'];
		}
	}

	return $min;
}

/** 
 * Get the sum of values of an agent module in a period of time.
 * 
 * @param int Agent module id to get the sumatory.
 * @param int Period of time to check (in seconds)
 * @param int Top date to check the values. Default current time.
 * 
 * @return float The sumatory of the module values in the interval.
 */
function get_agentmodule_data_sum ($id_agent_module, $period, $date = 0) {

	// Initialize variables
	if (empty ($date)) $date = get_system_time ();
	if ((empty ($period)) OR ($period == 0)) $period = $config["sla_period"];
	$datelimit = $date - $period;
	
	$id_module_type = get_db_value ('id_tipo_modulo', 'tagente_modulo','id_agente_modulo', $id_agent_module);
	$module_name = get_db_value ('nombre', 'ttipo_modulo', 'id_tipo', $id_module_type);
	$module_interval = get_module_interval ($id_agent_module);

    // Wrong module type
	if (is_module_data_string ($module_name)) {
		return 0;
	}

	// Incremental modules are treated differently
	$module_inc = is_module_inc ($module_name);

	// Get module data
	$interval_data = get_db_all_rows_sql ('SELECT * FROM tagente_datos 
			WHERE id_agente_modulo = ' . (int) $id_agent_module .
			' AND utimestamp > ' . (int) $datelimit .
			' AND utimestamp < ' . (int) $date .
			' ORDER BY utimestamp ASC', true);
	if ($interval_data === false) $interval_data = array ();

	// Get previous data
	$previous_data = get_previous_data ($id_agent_module, $datelimit);
	if ($previous_data !== false) {
		$previous_data['utimestamp'] = $datelimit;
		array_unshift ($interval_data, $previous_data);
	}

	// Get next data
	$next_data = get_next_data ($id_agent_module, $date);
	if ($next_data !== false) {
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	} else if (count ($interval_data) > 0) {
		// Propagate the last known data to the end of the interval
		$next_data = array_pop ($interval_data);
		array_push ($interval_data, $next_data);
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	}

	if (count ($interval_data) < 2) {
		return false;
	}

	// Set initial conditions
	$total = 0;
	$previous_data = array_shift ($interval_data);
	foreach ($interval_data as $data) {
		if ($module_inc) {
			$total += $previous_data['datos'] * ($data['utimestamp'] -  $previous_data['utimestamp']);
		} else {
			$total += $previous_data['datos'] * ($data['utimestamp'] -  $previous_data['utimestamp']) / $module_interval;
		}
		$previous_data = $data;
	}

	return $total;
}

/** 
 * Get SLA of a module.
 * 
 * @param int Agent module to calculate SLA
 * @param int Period to check the SLA compliance.
 * @param int Minimum data value the module in the right interval
 * @param int Maximum data value the module in the right interval. False will
 * ignore max value
 * @param int Beginning date of the report in UNIX time (current date by default).
 * 
 * @return float SLA percentage of the requested module. False if no data were
 * found
 */
function get_agentmodule_sla ($id_agent_module, $period = 0, $min_value = 1, $max_value = false, $date = 0) {
	global $config;
	
	// Initialize variables
	if (empty ($date)) $date = get_system_time ();
	if ((empty ($period)) OR ($period == 0)) $period = $config["sla_period"];
	
	// Limit date to start searching data
	$datelimit = $date - $period;
	
	// Get interval data
	$sql = sprintf ('SELECT * FROM tagente_datos
	                 WHERE id_agente_modulo = %d
	                 AND utimestamp > %d AND utimestamp <= %d',
	                 $id_agent_module, $datelimit, $date);
	$interval_data = get_db_all_rows_sql ($sql, true);
	if ($interval_data === false) $interval_data = array ();
	
	// Get previous data
	$previous_data = get_previous_data ($id_agent_module, $datelimit);
	if ($previous_data !== false) {
		$previous_data['utimestamp'] = $datelimit;
		array_unshift ($interval_data, $previous_data);
	}

	// Get next data
	$next_data = get_next_data ($id_agent_module, $date);
	if ($next_data !== false) {
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	} else if (count ($interval_data) > 0) {
		// Propagate the last known data to the end of the interval
		$next_data = array_pop ($interval_data);
		array_push ($interval_data, $next_data);
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	}

	if (count ($interval_data) < 2) {
		return false;
	}

	// Set initial conditions
	$bad_period = 0;
	$first_data = array_shift ($interval_data);
	$previous_utimestamp = $first_data['utimestamp'];
	if ((($max_value > $min_value AND ($first_data['datos'] > $max_value OR  $first_data['datos'] < $min_value))) OR
	     ($max_value <= $min_value AND $first_data['datos'] < $min_value)) {
		$previous_status = 1;	
	} else {
		$previous_status = 0;
	}

	foreach ($interval_data as $data) {
		// Previous status was critical
		if ($previous_status == 1) {
			$bad_period += $data['utimestamp'] - $previous_utimestamp;
		}
		
		if (array_key_exists('datos', $data)) {
			// Re-calculate previous status for the next data
			if ((($max_value > $min_value AND ($data['datos'] > $max_value OR  $data['datos'] < $min_value))) OR
			     ($max_value <= $min_value AND $data['datos'] < $min_value)) {
				$previous_status = 1;
			}
			else {
				$previous_status = 0;
			}
		}
		
		$previous_utimestamp = $data['utimestamp'];
	}
	
	// Return the percentage of SLA compliance
	return (float) (100 - ($bad_period / $period) * 100);
}

/** 
 * Get general statistical info on a group
 * 
 * @param int Group Id to get info from. 0 = all
 * 
 * @return array Group statistics
 */
function get_group_stats ($id_group = 0) {
	global $config;

	$data = array ();
	$data["monitor_checks"] = 0;
	$data["monitor_not_init"] = 0;
	$data["monitor_unknown"] = 0;
	$data["monitor_ok"] = 0;
	$data["monitor_bad"] = 0; // Critical + Unknown + Warning
	$data["monitor_warning"] = 0;
	$data["monitor_critical"] = 0;
	$data["monitor_alerts"] = 0;
	$data["monitor_alerts_fired"] = 0;
	$data["monitor_alerts_fire_count"] = 0;
	$data["total_agents"] = 0;
	$data["total_alerts"] = 0;
	$data["total_checks"] = 0;
	$data["alerts"] = 0;
	$data["agents_unknown"] = 0;
	$data["monitor_health"] = 100;
	$data["alert_level"] = 100;
	$data["module_sanity"] = 100;
	$data["server_sanity"] = 100;
	$data["total_not_init"] = 0;
	$data["monitor_non_init"] = 0;

	$cur_time = get_system_time ();

	//Check for access credentials using give_acl. More overhead, much safer
	if (!give_acl ($config["id_user"], $id_group, "AR")) {
		return $data;
	}
	
	if ($id_group == 0) {
		$id_group = array_keys (get_user_groups ());
	}

	// -------------------------------------------------------------------
	// Server processed stats. NOT realtime (taken from tgroup_stat)
	// -------------------------------------------------------------------
	if ($config["realtimestats"] == 0){

		if (!is_array($id_group)){
			$my_group = $id_group;
			$id_group = array();
			$id_group[0] = $my_group;
		}

		foreach ($id_group as $group){
			$group_stat = get_db_all_rows_sql ("SELECT * FROM tgroup_stat, tgrupo WHERE tgrupo.id_grupo = tgroup_stat.id_group AND tgroup_stat.id_group = $group ORDER BY nombre");
			$data["monitor_checks"] += $group_stat[0]["modules"];
			$data["monitor_not_init"] += $group_stat[0]["non-init"];
			$data["monitor_unknown"] += $group_stat[0]["unknown"];
			$data["monitor_ok"] += $group_stat[0]["normal"];
			$data["monitor_warning"] += $group_stat[0]["warning"];
			$data["monitor_critical"] += $group_stat[0]["critical"];
			$data["monitor_alerts"] += $group_stat[0]["alerts"];
			$data["monitor_alerts_fired"] += $group_stat[0]["alerts_fired"];
			$data["monitor_alerts_fire_count"] += $group_stat[0]["alerts_fired"];
			$data["total_checks"] += $group_stat[0]["modules"];
			$data["total_alerts"] += $group_stat[0]["alerts"];
			$data["total_agents"] += $group_stat[0]["agents"];
			$data["agents_unknown"] += $group_stat[0]["agents_unknown"];
			$data["utimestamp"] = $group_stat[0]["utimestamp"];
		}

	// -------------------------------------------------------------------
	// Realtime stats, done by PHP Console
	// -------------------------------------------------------------------
	} else {

		if (!is_array($id_group)){
			$my_group = $id_group;
			$id_group = array();
			$id_group[0] = $my_group;
		}

		foreach ($id_group as $group){


			$data["agents_unknown"] += get_db_sql ("SELECT COUNT(*) FROM tagente WHERE id_grupo = $group AND disabled = 0 AND ultimo_contacto < NOW() - (intervalo *2)");

			$data["total_agents"] += get_db_sql ("SELECT COUNT(*) FROM tagente WHERE id_grupo = $group AND disabled = 0");

			$data["monitor_checks"] += get_db_sql ("SELECT COUNT(tagente_estado.id_agente_estado) FROM tagente_estado, tagente, tagente_modulo WHERE tagente.id_grupo = $group AND tagente.disabled = 0 AND tagente_estado.id_agente = tagente.id_agente AND tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo AND tagente_modulo.disabled = 0");

			$data["total_not_init"] += get_db_sql ("SELECT COUNT(tagente_estado.id_agente_estado) FROM tagente_estado, tagente, tagente_modulo WHERE tagente.id_grupo = $group AND tagente.disabled = 0 AND tagente_estado.id_agente = tagente.id_agente AND tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo AND tagente_modulo.disabled = 0
	 AND tagente_modulo.id_tipo_modulo NOT IN (21,22,23,24) AND tagente_estado.utimestamp = 0");

			$data["monitor_ok"] += get_db_sql ("SELECT COUNT(tagente_estado.id_agente_estado) FROM tagente_estado, tagente, tagente_modulo WHERE tagente.id_grupo = $group AND tagente.disabled = 0 AND tagente_estado.id_agente = tagente.id_agente AND tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo AND tagente_modulo.disabled = 0 AND estado = 0 AND ((UNIX_TIMESTAMP(NOW()) - tagente_estado.utimestamp) < (tagente_estado.current_interval * 2) OR (tagente_modulo.id_tipo_modulo IN(21,22,23,24,100))) AND (utimestamp > 0 OR (tagente_modulo.id_tipo_modulo IN(21,22,23,24)))");

			$data["monitor_critical"] += get_db_sql ("SELECT COUNT(tagente_estado.id_agente_estado) FROM tagente_estado, tagente, tagente_modulo WHERE tagente.id_grupo = $group AND tagente.disabled = 0 AND tagente_estado.id_agente = tagente.id_agente AND tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo AND tagente_modulo.disabled = 0 AND estado = 1 AND ((UNIX_TIMESTAMP(NOW()) - tagente_estado.utimestamp) < (tagente_estado.current_interval * 2) OR (tagente_modulo.id_tipo_modulo IN(21,22,23,24,100))) AND utimestamp > 0");
			
			$data["monitor_warning"] += get_db_sql ("SELECT COUNT(tagente_estado.id_agente_estado) FROM tagente_estado, tagente, tagente_modulo WHERE tagente.id_grupo = $group AND tagente.disabled = 0 AND tagente_estado.id_agente = tagente.id_agente AND tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo AND tagente_modulo.disabled = 0 AND estado = 2 AND ((UNIX_TIMESTAMP(NOW()) - tagente_estado.utimestamp) < (tagente_estado.current_interval * 2) OR (tagente_modulo.id_tipo_modulo IN(21,22,23,24,100))) AND utimestamp > 0");

			$data["monitor_unknown"] += get_db_sql ("SELECT COUNT(tagente_estado.id_agente_estado) FROM tagente_estado, tagente, tagente_modulo WHERE tagente.id_grupo = $group AND tagente.disabled = 0 AND tagente.id_agente = tagente_estado.id_agente  AND tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo AND tagente_modulo.disabled = 0 AND utimestamp > 0 AND tagente_modulo.id_tipo_modulo NOT IN(21,22,23,24,100) AND (UNIX_TIMESTAMP(NOW()) - tagente_estado.utimestamp) >= (tagente_estado.current_interval * 2)");

			$data["monitor_not_init"] += get_db_sql ("SELECT COUNT(tagente_estado.id_agente_estado) FROM tagente_estado, tagente, tagente_modulo WHERE tagente.id_grupo = $group AND tagente.disabled = 0 AND tagente.id_agente = tagente_estado.id_agente  AND tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo AND tagente_modulo.disabled = 0 AND tagente_modulo.id_tipo_modulo NOT IN (21,22,23,24) AND utimestamp = 0");

			$data["monitor_alerts"] += get_db_sql ("SELECT COUNT(talert_template_modules.id) FROM talert_template_modules, tagente_modulo, tagente_estado, tagente WHERE tagente.id_grupo = $group AND tagente_modulo.id_agente = tagente.id_agente AND tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo AND tagente_modulo.disabled = 0 AND tagente.disabled = 0 AND talert_template_modules.id_agent_module = tagente_modulo.id_agente_modulo");

			$data["monitor_alerts_fired"] += get_db_sql ("SELECT COUNT(talert_template_modules.id) FROM talert_template_modules, tagente_modulo, tagente_estado, tagente WHERE tagente.id_grupo = $group AND tagente_modulo.id_agente = tagente.id_agente AND tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo AND tagente_modulo.disabled = 0 AND tagente.disabled = 0 AND talert_template_modules.id_agent_module = tagente_modulo.id_agente_modulo AND times_fired > 0");
		}
		/*
		 Monitor health (percentage)
		 Data health (percentage)
		 Global health (percentage)
		 Module sanity (percentage)
		 Alert level (percentage)
		 
		 Server Sanity	0% Uninitialized modules
		 
		 */
	}

	if ($data["monitor_unknown"] > 0 && $data["monitor_checks"] > 0) {
		$data["monitor_health"] = format_numeric (100 - ($data["monitor_unknown"] / ($data["monitor_checks"] / 100)), 1);
	} else {
		$data["monitor_health"] = 100;
	}

	if ($data["monitor_not_init"] > 0 && $data["monitor_checks"] > 0) {
		$data["module_sanity"] = format_numeric (100 - ($data["monitor_not_init"] / ($data["monitor_checks"] / 100)), 1);
	} else {
		$data["module_sanity"] = 100;
	}

	if (isset($data["alerts"])){
		if ($data["monitor_alerts_fired"] > 0 && $data["alerts"] > 0) {
			$data["alert_level"] = format_numeric (100 - ($data	["monitor_alerts_fired"] / ($data["alerts"] / 100)), 1);
		} else {
			$data["alert_level"] = 100;
		}
	} 
 	else {
		$data["alert_level"] = 100;
		$data["alerts"] = 0;
	}

	$data["monitor_bad"] = $data["monitor_critical"] + $data["monitor_warning"];

	if ($data["monitor_bad"] > 0 && $data["monitor_checks"] > 0) {
		$data["global_health"] = format_numeric (100 - ($data["monitor_bad"] / ($data["monitor_checks"] / 100)), 1);
	} else {
		$data["global_health"] = 100;
	}

	$data["server_sanity"] = format_numeric (100 - $data["module_sanity"], 1);

	return ($data);

}


/** 
 * Get an event reporting table.
 *
 * It construct a table object with all the events happened in a group
 * during a period of time.
 * 
 * @param int Group id to get the report.
 * @param int Period of time to get the report.
 * @param int Beginning date of the report
 * @param int Flag to return or echo the report table (echo by default).
 * 
 * @return object A table object
 */
function event_reporting ($id_group, $period, $date = 0, $return = false) {
	if (empty ($date)) {
		$date = get_system_time ();
	} elseif (!is_numeric ($date)) {
		$date = strtotime ($date);
	}
	
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('Status');
	$table->head[1] = __('Event name');
	$table->head[2] = __('User ID');
	$table->head[3] = __('Timestamp');
	
	$events = get_group_events ($id_group, $period, $date);
	if (empty ($events)) {
		$events = array ();
	}
	foreach ($events as $event) {
		$data = array ();
		if ($event["estado"] == 0)
			$data[0] = '<img src="images/dot_red.png" />';
		else
			$data[0] = '<img src="images/dot_green.png" />';
		$data[1] = $event['evento'];
		$data[2] = $event['id_usuario'] != '0' ? $event['id_usuario'] : '';
		$data[3] = $event["timestamp"];
		array_push ($table->data, $data);
	}

	if (empty ($return))
		print_table ($table);
	return $table;
}

/** 
 * Get a table report from a alerts fired array.
 * 
 * @param array Alerts fired array. 
 * @see function get_alerts_fired ()
 * 
 * @return object A table object with a report of the fired alerts.
 */
function get_fired_alerts_reporting_table ($alerts_fired) {
	$agents = array ();
	global $config;

	require_once ($config["homedir"].'/include/functions_alerts.php');
	
	foreach (array_keys ($alerts_fired) as $id_alert) {
		$alert_module = get_alert_agent_module ($id_alert);
		$template = get_alert_template ($id_alert);
		
		/* Add alerts fired to $agents_fired_alerts indexed by id_agent */
		$id_agent = get_db_value ('id_agente', 'tagente_modulo',
			'id_agente_modulo', $alert_module['id_agent_module']);
		if (!isset ($agents[$id_agent])) {
			$agents[$id_agent] = array ();
		}
		array_push ($agents[$id_agent], array ($alert_module, $template));
	}
	
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('Agent');
	$table->head[1] = __('Alert description');
	$table->head[2] = __('Times fired');
	$table->head[3] = __('Priority');
	
	foreach ($agents as $id_agent => $alerts) {
		$data = array ();
		foreach ($alerts as $tuple) {
			$alert_module = $tuple[0];
			$template = $tuple[1];
			if (! isset ($data[0]))
				$data[0] = get_agent_name ($id_agent);
			else
				$data[0] = '';
			$data[1] = $template['name'];
			$data[2] = $alerts_fired[$alert_module['id']];
			$data[3] = get_alert_priority ($alert_module['priority']);
			array_push ($table->data, $data);
		}
	}
	
	return $table;
}

/**
 * Get a report for alerts of agent.
 *
 * It prints the numbers of alerts defined, fired and not fired of agent.
 *
 * @param int $id_agent Agent to get info of the alerts.
 * @param int $period Period of time of the desired alert report.
 * @param int $date Beggining date of the report (current date by default).
 * @param bool $return Flag to return or echo the report (echo by default).
 * @param bool Flag to return the html or table object, by default html.
 * 
 * @return mixed A table object (XHTML) or object table is false the html.
 */
function alert_reporting_agent ($id_agent, $period = 0, $date = 0, $return = true, $html = true) {
	if (!is_numeric ($date)) {
		$date = strtotime ($date);
	}
	if (empty ($date)) {
		$date = get_system_time ();
	}
	if (empty ($period)) {
		global $config;
		$period = $config["sla_period"];
	}
	
	$table->width = '99%';
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('Module');
	$table->head[1] = __('Template');
	$table->head[2] = __('Actions');
	$table->head[3] = __('Fired');
	
	$alerts = get_agent_alerts ($id_agent);
	
	if (isset($alerts['simple'])) {
		$i = 0;
		foreach ($alerts['simple'] as $alert) {
			$data = array();
			$data[0] = get_db_value_filter('nombre', 'tagente_modulo', array('id_agente_modulo' => $alert['id_agent_module']));
			$data[1] = get_db_value_filter('name', 'talert_templates', array('id' => $alert['id_alert_template']));
			$actions = get_db_all_rows_sql('SELECT name 
				FROM talert_actions 
				WHERE id IN (SELECT id_alert_action 
					FROM talert_template_module_actions 
					WHERE id_alert_template_module = ' . $alert['id'] . ');');
			$data[2] = '<ul class="action_list">';
			if ($actions === false) {
				$actions = array();
			}
			foreach ($actions as $action) {
				$data[2] .= '<li>' . $action['name'] . '</li>';
			}
			$data[2] .= '</ul>';
			
			$data[3] = '<ul style="list-style-type: disc; margin-left: 10px;">';
			$firedTimes = get_agent_alert_fired($id_agent, $alert['id'], (int) $period, (int) $date);
			if ($firedTimes === false) {
				$firedTimes = array();
			}
			foreach ($firedTimes as $fireTime) {
				$data[3] .= '<li>' . $fireTime['timestamp'] . '</li>';
			}
			$data[3] .= '</ul>';
			
			if ($alert['disabled']) {
				$table->rowstyle[$i] = 'color: grey; font-style: italic;';
			}
			$i++;
			
			array_push ($table->data, $data);
		}
	}
	
	if ($html) {
		return print_table ($table, $return);
	}
	else {
		return $table;
	}
}

/**
 * Get a report for alerts of module.
 *
 * It prints the numbers of alerts defined, fired and not fired of agent.
 *
 * @param int $id_agent_module Module to get info of the alerts.
 * @param int $period Period of time of the desired alert report.
 * @param int $date Beggining date of the report (current date by default).
 * @param bool $return Flag to return or echo the report (echo by default).
 * @param bool Flag to return the html or table object, by default html.
 * 
 * @return mixed A table object (XHTML) or object table is false the html.
 */
function alert_reporting_module ($id_agent_module, $period = 0, $date = 0, $return = true, $html = true) {
	if (!is_numeric ($date)) {
		$date = strtotime ($date);
	}
	if (empty ($date)) {
		$date = get_system_time ();
	}
	if (empty ($period)) {
		global $config;
		$period = $config["sla_period"];
	}
	
	$table->width = '99%';
	$table->data = array ();
	$table->head = array ();
	$table->head[1] = __('Template');
	$table->head[2] = __('Actions');
	$table->head[3] = __('Fired');
	
	
	$alerts = get_db_all_rows_sql('SELECT *
		FROM talert_template_modules AS t1
			INNER JOIN talert_templates AS t2 ON t1.id = t2.id
		WHERE id_agent_module = ' . $id_agent_module);
	
	$i = 0;
	foreach ($alerts as $alert) {
		$data = array();
		$data[1] = get_db_value_filter('name', 'talert_templates', array('id' => $alert['id_alert_template']));
		$actions = get_db_all_rows_sql('SELECT name 
			FROM talert_actions 
			WHERE id IN (SELECT id_alert_action 
				FROM talert_template_module_actions 
				WHERE id_alert_template_module = ' . $alert['id'] . ');');
		$data[2] = '<ul class="action_list">';
		if ($actions === false) {
			$actions = array();
		}
		foreach ($actions as $action) {
			$data[2] .= '<li>' . $action['name'] . '</li>';
		}
		$data[2] .= '</ul>';
		
		$data[3] = '<ul style="list-style-type: disc; margin-left: 10px;">';
		$firedTimes = get_module_alert_fired($id_agent_module, $alert['id'], (int) $period, (int) $date);
		if ($firedTimes === false) {
			$firedTimes = array();
		}
		foreach ($firedTimes as $fireTime) {
			$data[3] .= '<li>' . $fireTime['timestamp'] . '</li>';
		}
		$data[3] .= '</ul>';
		
		if ($alert['disabled']) {
			$table->rowstyle[$i] = 'color: grey; font-style: italic;';
		}
		$i++;
		
		array_push ($table->data, $data);
	}
	
	if ($html) {
		return print_table ($table, $return);
	}
	else {
		return $table;
	}
}

/**
 * Get a report for alerts in a group of agents.
 *
 * It prints the numbers of alerts defined, fired and not fired in a group.
 * It also prints all the alerts that were fired grouped by agents.
 *
 * @param int $id_group Group to get info of the alerts.
 * @param int $period Period of time of the desired alert report.
 * @param int $date Beggining date of the report (current date by default).
 * @param bool $return Flag to return or echo the report (echo by default).
 *
 * @return string
 */
function alert_reporting ($id_group, $period = 0, $date = 0, $return = false) {
	$output = '';
	$alerts = get_group_alerts ($id_group);
	$alerts_fired = get_alerts_fired ($alerts, $period, $date);
	
	$fired_percentage = 0;
	if (sizeof ($alerts) > 0)
		$fired_percentage = round (sizeof ($alerts_fired) / sizeof ($alerts) * 100, 2);
	$not_fired_percentage = 100 - $fired_percentage;
	$output .= '<img src="include/fgraph.php?tipo=alerts_fired_pipe&height=150&width=280&fired='.
		$fired_percentage.'&not_fired='.$not_fired_percentage.'" style="float: right; border: 1px solid black">';
	
	$output .= '<strong>'.__('Alerts fired').': '.sizeof ($alerts_fired).'</strong><br />';
	$output .= '<strong>'.__('Total alerts monitored').': '.sizeof ($alerts).'</strong><br />';

	if (! sizeof ($alerts_fired)) {
		if (!$return)
			echo $output;
		return $output;
	}
	$table = get_fired_alerts_reporting_table ($alerts_fired);
	$table->width = '100%';
	$table->class = 'databox';
	$table->size = array ();
	$table->size[0] = '100px';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold';
	
	$output .= print_table ($table, true);
	
	if (!$return)
		echo $output;
	return $output;
}

/**
 * Get a report for monitors modules in a group of agents.
 *
 * It prints the numbers of monitors defined, showing those which went up and down, in a group.
 * It also prints all the down monitors in the group.
 *
 * @param int $id_group Group to get info of the monitors.
 * @param int $period Period of time of the desired monitor report.
 * @param int $date Beginning date of the report in UNIX time (current date by default).
 * @param bool $return Flag to return or echo the report (by default).
 *
 * @return string
 */
function monitor_health_reporting ($id_group, $period = 0, $date = 0, $return = false) {
	if (empty ($date)) //If date is 0, false or empty
		$date = get_system_time ();
		
	$datelimit = $date - $period;
	$output = '';
	
	$monitors = get_monitors_in_group ($id_group);
	if (empty ($monitors)) //If monitors has returned false or an empty array
		return;
	$monitors_down = get_monitors_down ($monitors, $period, $date);
	$down_percentage = round (count ($monitors_down) / count ($monitors) * 100, 2);
	$not_down_percentage = 100 - $down_percentage;
	
	$output .= '<strong>'.__('Total monitors').': '.count ($monitors).'</strong><br />';
	$output .= '<strong>'.__('Monitors down on period').': '.count ($monitors_down).'</strong><br />';
	
	$table = get_monitors_down_reporting_table ($monitors_down);
	$table->width = '100%';
	$table->class = 'databox';
	$table->size = array ();
	$table->size[0] = '100px';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold';
	
	$table->size = array ();
	$table->size[0] = '100px';
	
	$output .= print_table ($table, true);
	
	//Floating it was ugly, moved it to the bottom
	$output .= '<img src="include/fgraph.php?tipo=monitors_health_pipe&height=150&width=280&down='.$down_percentage.'&amp;not_down='.$not_down_percentage.'" style="border: 1px solid black" />';
	
	if (!$return)
		echo $output;
	return $output;
}

/** 
 * Get a report table with all the monitors down.
 * 
 * @param array  An array with all the monitors down
 * @see function get_monitors_down()
 * 
 * @return object A table object with a monitors down report.
 */
function get_monitors_down_reporting_table ($monitors_down) {
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('Agent');
	$table->head[1] = __('Monitor');
	
	$agents = array ();
	if ($monitors_down){
		foreach ($monitors_down as $monitor) {
			/* Add monitors fired to $agents_fired_alerts indexed by id_agent */
			$id_agent = $monitor['id_agente'];
			if (!isset ($agents[$id_agent])) {
				$agents[$id_agent] = array ();
			}
			array_push ($agents[$id_agent], $monitor);
			
			$monitors_down++;
		}
		foreach ($agents as $id_agent => $monitors) {
			$data = array ();
			foreach ($monitors as $monitor) {
				if (! isset ($data[0]))
					$data[0] = get_agent_name ($id_agent);
				else
					$data[0] = '';
				if ($monitor['descripcion'] != '') {
					$data[1] = $monitor['descripcion'];
				} else {
					$data[1] = $monitor['nombre'];
				}
				array_push ($table->data, $data);
			}
		}
	}
	return $table;
}

/**
 * Get a general report of a group of agents.
 *
 * It shows the number of agents and no more things right now. 
 *
 * @param int Group to get the report
 * @param bool Flag to return or echo the report (by default).
 * 
 * @return HTML string with group report
 */
function print_group_reporting ($id_group, $return = false) {
	$agents = get_group_agents ($id_group, false, "none");
	$output = '<strong>'.__('Agents in group').': '.count ($agents).'</strong><br />';
	
	if ($return === false)
		echo $output;
		
	return $output;
}

/** 
 * Get a report table of the fired alerts group by agents.
 * 
 * @param int Agent id to generate the report.
 * @param int Period of time of the report.
 * @param int Beginning date of the report in UNIX time (current date by default).
 * 
 * @return object A table object with the alert reporting..
 */
function get_agent_alerts_reporting_table ($id_agent, $period = 0, $date = 0) {
	global $config;
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('Type');
	$table->head[1] = __('Description');
	$table->head[2] = __('Value');
	$table->head[3] = __('Threshold');
	$table->head[4] = __('Last fired');
	$table->head[5] = __('Times fired');
	
	require_once ($config["homedir"].'/include/functions_alerts.php');
	
	$alerts = get_agent_alerts ($id_agent);
	/* FIXME: Add compound alerts to the report. Some extra code is needed here */
	foreach ($alerts['simple'] as $alert) {
		$fires = get_alert_fires_in_period ($alert['id'], $period, $date);
		if (! $fires) {
			continue;
		}
		
		$template = get_alert_template ($alert['id_alert_template']);
		$data = array ();
		$data[0] = get_alert_templates_type_name ($template['type']);
		$data[1] = $template['name'];
		
		switch ($template['type']) {
		case 'regex':
			if ($template['matches_value'])
				$data[2] = '&#8771; "'.$template['value'].'"';
			else
				$data[2] = '&#8772; "'.$template['value'].'"';
			break;
		case 'equal':
		case 'not_equal':
			$data[2] = $template['value'];
			
			break;
		case 'max-min':
			$data[2] = __('Min.').': '.$template['min_value']. ' ';
			$data[2] .= __('Max.').': '.$template['max_value']. ' ';
			
			break;
		case 'max':
			$data[2] = $template['max_value'];
			
			break;
		case 'min':
			$data[2] = $template['min_value'];
			
			break;
		}
		$data[3] = $template['time_threshold'];
		$data[4] = print_timestamp (get_alert_last_fire_timestamp_in_period ($alert['id'], $period, $date), true);
		$data[5] = $fires;
		
		array_push ($table->data, $data);
	}
	return $table;
}

/** 
 * Get a report of monitors in an agent.
 * 
 * @param int Agent id to get the report
 * @param int Period of time of the report.
 * @param int Beginning date of the report in UNIX time (current date by default).
 * 
 * @return object A table object with the report.
 */
function get_agent_monitors_reporting_table ($id_agent, $period = 0, $date = 0) {
	$n_a_string = __('N/A').'(*)';
	$table->head = array ();
	$table->head[0] = __('Monitor');
	$table->head[1] = __('Last failure');
	$table->data = array ();
	$monitors = get_monitors_in_agent ($id_agent);
	
	if ($monitors === false) {
		return $table;
	}
	foreach ($monitors as $monitor) {
		$downs = get_monitor_downs_in_period ($monitor['id_agente_modulo'], $period, $date);
		if (! $downs) {
			continue;
		}
		$data = array ();
		if ($monitor['descripcion'] != $n_a_string && $monitor['descripcion'] != '')
			$data[0] = $monitor['descripcion'];
		else
			$data[0] = $monitor['nombre'];
		$data[1] = get_monitor_last_down_timestamp_in_period ($monitor['id_agente_modulo'], $period, $date);
		array_push ($table->data, $data);
	}
	
	return $table;
}

/** 
 * Get a report of all the modules in an agent.
 * 
 * @param int Agent id to get the report.
 * @param int Period of time of the report
 * @param int Beginning date of the report in UNIX time (current date by default).
 * 
 * @return object
 */
function get_agent_modules_reporting_table ($id_agent, $period = 0, $date = 0) {
	$table->data = array ();
	$n_a_string = __('N/A').'(*)';
	$modules = get_agent_modules ($id_agent, array ("nombre", "descripcion"));
	if ($modules === false)
		$modules = array();
	$data = array ();
	
	foreach ($modules as $module) {
		if ($module['descripcion'] != $n_a_string && $module['descripcion'] != '')
			$data[0] = $module['descripcion'];
		else
			$data[0] = $module['nombre'];
		array_push ($table->data, $data);
	}
	
	return $table;
}

/**
 * Get a detailed report of an agent
 *
 * @param int Agent to get the report.
 * @param int Period of time of the desired report.
 * @param int Beginning date of the report in UNIX time (current date by default).
 * @param bool Flag to return or echo the report (by default).
 *
 * @return string
 */
function get_agent_detailed_reporting ($id_agent, $period = 0, $date = 0, $return = false) {
	$output = '';
	$n_a_string = __('N/A').'(*)';
	
	/* Show modules in agent */
	$output .= '<div class="agent_reporting">';
	$output .= '<h3 style="text-decoration: underline">'.__('Agent').' - '.get_agent_name ($id_agent).'</h3>';
	$output .= '<h4>'.__('Modules').'</h3>';
	$table_modules = get_agent_modules_reporting_table ($id_agent, $period, $date);
	$table_modules->width = '99%';
	$output .= print_table ($table_modules, true);
	
	/* Show alerts in agent */
	$table_alerts = get_agent_alerts_reporting_table ($id_agent, $period, $date);
	$table_alerts->width = '99%';
	if (sizeof ($table_alerts->data)) {
		$output .= '<h4>'.__('Alerts').'</h4>';
		$output .= print_table ($table_alerts, true);
	}
	
	/* Show monitor status in agent (if any) */
	$table_monitors = get_agent_monitors_reporting_table ($id_agent, $period, $date);
	if (sizeof ($table_monitors->data) == 0) {
		$output .= '</div>';
		if (! $return)
			echo $output;
		return $output;
	}
	$table_monitors->width = '99%';
	$table_monitors->align = array ();
	$table_monitors->align[1] = 'right';
	$table_monitors->size = array ();
	$table_monitors->align[1] = '10%';
	$output .= '<h4>'.__('Monitors').'</h4>';
	$output .= print_table ($table_monitors, true);
	
	$output .= '</div>';
	
	if (! $return)
		echo $output;
	return $output;
}

/**
 * Get a detailed report of agents in a group.
 *
 * @param mixed Group(s) to get the report
 * @param int Period
 * @param int Timestamp to start from
 * @param bool Flag to return or echo the report (by default).
 *
 * @return string
 */
function get_group_agents_detailed_reporting ($id_group, $period = 0, $date = 0, $return = false) {
	$agents = get_group_agents ($id_group, false, "none");
	
	$output = '';
	foreach ($agents as $agent_id => $agent_name) {
		$output .= get_agent_detailed_reporting ($agent_id, $period, $date, true);
	}
	
	if ($return === false)
		echo $output;
	
	return $output;
}


/** 
 * Get a detailed report of summarized events per agent
 *
 * It construct a table object with all the grouped events happened in an agent
 * during a period of time.
 * 
 * @param mixed Agent id(s) to get the report from.
 * @param int Period of time (in seconds) to get the report.
 * @param int Beginning date (unixtime) of the report
 * @param bool Flag to return or echo the report table (echo by default).
 * 
 * @return A table object (XHTML)
 */
function get_agents_detailed_event_reporting ($id_agents, $period = 0, $date = 0, $return = false) {
	$id_agents = (array)safe_int ($id_agents, 1);
	
	if (!is_numeric ($date)) {
		$date = strtotime ($date);
	}
	if (empty ($date)) {
		$date = get_system_time ();
	}
	if (empty ($period)) {
		global $config;
		$period = $config["sla_period"];
	}
	
	$table->width = '99%';
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('Event name');
	$table->head[1] = __('Event type');
	$table->head[2] = __('Criticity');
	$table->head[3] = __('Count');
	$table->head[4] = __('Timestamp');
	
	$events = array ();
	
	foreach ($id_agents as $id_agent) {
		$event = get_agent_events ($id_agent, (int) $period, (int) $date);
		if (!empty ($event)) {
			array_push ($events, $event);
		}
	}
	
	if ($events)
	foreach ($events as $eventRow) {
		foreach ($eventRow as $event) { 
			$data = array ();
			$data[0] = $event['evento'];
			$data[1] = $event['event_type'];
			$data[2] = get_priority_name ($event['criticity']);
			$data[3] = $event['count_rep'];
			$data[4] = $event['time2'];
			array_push ($table->data, $data);
		}
	}

	if ($events)	
		return print_table ($table, $return);
}

/**
 * 
 * @param unknown_type $id_group
 * @param unknown_type $period
 * @param unknown_type $date
 * @param unknown_type $return
 * @param unknown_type $html
 */
function get_group_detailed_event_reporting ($id_group, $period = 0, $date = 0, $return = false, $html = true) {
	if (!is_numeric ($date)) {
		$date = strtotime ($date);
	}
	if (empty ($date)) {
		$date = get_system_time ();
	}
	if (empty ($period)) {
		global $config;
		$period = $config["sla_period"];
	}
	
	$table->width = '99%';
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('Event name');
	$table->head[1] = __('Event type');
	$table->head[2] = __('Criticity');
	$table->head[3] = __('Timestamp');
	
	$events = get_group_events($id_group, $period, $date);
	
	if ($events)
		foreach ($events as $event) {
			$data = array ();
			$data[0] = $event['evento'];
			$data[1] = $event['event_type'];
			$data[2] = get_priority_name ($event['criticity']);
			$data[3] = $event['timestamp'];
			array_push ($table->data, $data);
		}
	
	if ($events) {
		if ($html) {
			return print_table ($table, $return);
		}
		else {
			return $table;
		}
	}
}

/** 
 * Get a detailed report of summarized events per agent
 *
 * It construct a table object with all the grouped events happened in an agent
 * during a period of time.
 * 
 * @param mixed Module id to get the report from.
 * @param int Period of time (in seconds) to get the report.
 * @param int Beginning date (unixtime) of the report
 * @param bool Flag to return or echo the report table (echo by default).
 * @param bool Flag to return the html or table object, by default html.
 * 
 * @return mixed A table object (XHTML) or object table is false the html.
 */
function get_module_detailed_event_reporting ($id_modules, $period = 0, $date = 0, $return = false, $html = true) {
	$id_modules = (array)safe_int ($id_modules, 1);
	
	if (!is_numeric ($date)) {
		$date = strtotime ($date);
	}
	if (empty ($date)) {
		$date = get_system_time ();
	}
	if (empty ($period)) {
		global $config;
		$period = $config["sla_period"];
	}
	
	$table->width = '99%';
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('Event name');
	$table->head[1] = __('Event type');
	$table->head[2] = __('Criticity');
	$table->head[3] = __('Count');
	$table->head[4] = __('Timestamp');
	
	$events = array ();
	
	foreach ($id_modules as $id_module) {
		$event = get_module_events ($id_module, (int) $period, (int) $date);
		if (!empty ($event)) {
			array_push ($events, $event);
		}
	}

	if ($events)
	foreach ($events as $eventRow) {
		foreach ($eventRow as $event) {
			$data = array ();
			$data[0] = $event['evento'];
			$data[1] = $event['event_type'];
			$data[2] = get_priority_name ($event['criticity']);
			$data[3] = $event['count_rep'];
			$data[4] = $event['time2'];
			array_push ($table->data, $data);
		}
	}
	
	if ($events) {
		if ($html) {
			return print_table ($table, $return);
		}
		else {
			return $table;
		}
	}
}

/** 
 * Get a detailed report of the modules of the agent
 * 
 * @param int $id_agent Agent id to get the report for.
 * 
 * @return array An array
 */
function get_agent_module_info ($id_agent) {
	global $config;
	
	$return = array ();
	$return["modules"] = 0; //Number of modules
	$return["monitor_normal"] = 0; //Number of 'good' monitors
	$return["monitor_warning"] = 0; //Number of 'warning' monitors
	$return["monitor_critical"] = 0; //Number of 'critical' monitors
	$return["monitor_down"] = 0; //Number of 'down' monitors
	$return["last_contact"] = 0; //Last agent contact 
	$return["interval"] = get_agent_interval ($id_agent); //How often the agent gets contacted
	$return["status_img"] = print_status_image (STATUS_AGENT_NO_DATA, __('Agent without data'), true);
	$return["alert_status"] = "notfired";
	$return["alert_img"] = print_status_image (STATUS_ALERT_NOT_FIRED, __('Alert not fired'), true);
	$return["agent_group"] = get_agent_group ($id_agent);
	
	if (!give_acl ($config["id_user"], $return["agent_group"], "AR")) {
		return $return;
	} 
	
	$sql = sprintf ("SELECT * FROM tagente_estado, tagente_modulo 
		WHERE tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo 
		AND tagente_modulo.disabled = 0 
		AND tagente_estado.utimestamp > 0 
		AND tagente_modulo.id_agente = %d", $id_agent);
	
	$modules = get_db_all_rows_sql ($sql, false, false);
	
	if ($modules === false) {
		return $return;
	}
	
	$now = get_system_time ();
	
	// Calculate modules for this agent
	foreach ($modules as $module) {
		$return["modules"]++;
		
		if ($module["module_interval"] > $return["interval"]) {
			$return["interval"] = $module["module_interval"];
		} elseif ($module["module_interval"] == 0) {
			$module["module_interval"] = $return["interval"];
		}
		
		if ($module["utimestamp"] > $return["last_contact"]) {
			$return["last_contact"] = $module["utimestamp"];
		}
		
		if (($module["id_tipo_modulo"] < 21 || $module["id_tipo_modulo"] > 23 ) AND ($module["id_tipo_modulo"] != 100)) {
			$async = 0;
		} else {
			$async = 1;
		}
		
		if ($async == 0 && ($module["utimestamp"] < ($now - $module["module_interval"] * 2))) {
			$return["monitor_down"]++;
		} elseif ($module["estado"] == 2) {
			$return["monitor_warning"]++;
		} elseif ($module["estado"] == 1) {
			$return["monitor_critical"]++;
		} else {
			$return["monitor_normal"]++;
		}
	}
		
	if ($return["modules"] > 0) {
		if ($return["modules"] == $return["monitor_down"])
			$return["status_img"] = print_status_image (STATUS_AGENT_DOWN, __('At least one module is in UKNOWN status'), true);	
		else if ($return["monitor_critical"] > 0)
			$return["status_img"] = print_status_image (STATUS_AGENT_CRITICAL, __('At least one module in CRITICAL status'), true);
		else if ($return["monitor_warning"] > 0)
			$return["status_img"] = print_status_image (STATUS_AGENT_WARNING, __('At least one module in WARNING status'), true);
		else
			$return["status_img"] = print_status_image (STATUS_AGENT_OK, __('All Monitors OK'), true);
	}
	
	//Alert not fired is by default
	if (give_disabled_group ($return["agent_group"])) {
		$return["alert_status"] = "disabled";
		$return["alert_img"] = print_status_image (STATUS_ALERT_DISABLED, __('Alert disabled'), true);
	} elseif (check_alert_fired ($id_agent) == 1) {
		$return["alert_status"] = "fired";
		$return["alert_img"] = print_status_image (STATUS_ALERT_FIRED, __('Alert fired'), true);	
	}
	
	return $return;
}	

/** 
 * Get a detailed report of the modules of the agent
 * 
 * @param int $id_agent Agent id to get the report for.
 * 
 * @return array An array
 */
function get_agent_module_info_with_filter ($id_agent,$filter = '') {
	global $config;
	
	$return = array ();
	$return["modules"] = 0; //Number of modules
	$return["monitor_normal"] = 0; //Number of 'good' monitors
	$return["monitor_warning"] = 0; //Number of 'warning' monitors
	$return["monitor_critical"] = 0; //Number of 'critical' monitors
	$return["monitor_down"] = 0; //Number of 'down' monitors
	$return["last_contact"] = 0; //Last agent contact 
	$return["interval"] = get_agent_interval ($id_agent); //How often the agent gets contacted
	$return["status_img"] = print_status_image (STATUS_AGENT_NO_DATA, __('Agent without data'), true);
	$return["alert_status"] = "notfired";
	$return["alert_img"] = print_status_image (STATUS_ALERT_NOT_FIRED, __('Alert not fired'), true);
	$return["agent_group"] = get_agent_group ($id_agent);
	
	if (!give_acl ($config["id_user"], $return["agent_group"], "AR")) {
		return $return;
	} 
	
	$sql = sprintf ("SELECT * FROM tagente_estado, tagente_modulo 
		WHERE tagente_estado.id_agente_modulo = tagente_modulo.id_agente_modulo 
		AND tagente_modulo.disabled = 0	
		AND tagente_estado.utimestamp > 0 
		AND tagente_modulo.id_agente = %d", $id_agent);
		
	$sql .= $filter;
	
	$modules = get_db_all_rows_sql ($sql);
	
	if ($modules === false) {
		return $return;
	}
	
	$now = get_system_time ();
	
	// Calculate modules for this agent
	foreach ($modules as $module) {
		$return["modules"]++;
		
		if ($module["module_interval"] > $return["interval"]) {
			$return["interval"] = $module["module_interval"];
		} elseif ($module["module_interval"] == 0) {
			$module["module_interval"] = $return["interval"];
		}
		
		if ($module["utimestamp"] > $return["last_contact"]) {
			$return["last_contact"] = $module["utimestamp"];
		}
		
		if (($module["id_tipo_modulo"] < 21 || $module["id_tipo_modulo"] > 23 ) AND  ($module["id_tipo_modulo"] != 100)) {
			$async = 0;
		} else {
			$async = 1;
		}
		
		if ($async == 0 && ($module["utimestamp"] < ($now - $module["module_interval"] * 2))) {
			$return["monitor_down"]++;
		} elseif ($module["estado"] == 2) {
			$return["monitor_warning"]++;
		} elseif ($module["estado"] == 1) {
			$return["monitor_critical"]++;
		} else {
			$return["monitor_normal"]++;
		}
	}
		
	if ($return["modules"] > 0) {
		if ($return["modules"] == $return["monitor_down"])
			$return["status_img"] = print_status_image (STATUS_AGENT_DOWN, __('At least one module is in UKNOWN status'), true);	
		else if ($return["monitor_critical"] > 0)
			$return["status_img"] = print_status_image (STATUS_AGENT_CRITICAL, __('At least one module in CRITICAL status'), true);
		else if ($return["monitor_warning"] > 0)
			$return["status_img"] = print_status_image (STATUS_AGENT_WARNING, __('At least one module in WARNING status'), true);
		else
			$return["status_img"] = print_status_image (STATUS_AGENT_OK, __('All Monitors OK'), true);
	}
	
	//Alert not fired is by default
	if (give_disabled_group ($return["agent_group"])) {
		$return["alert_status"] = "disabled";
		$return["alert_img"] = print_status_image (STATUS_ALERT_DISABLED, __('Alert disabled'), true);
	} elseif (check_alert_fired ($id_agent) == 1) {
		$return["alert_status"] = "fired";
		$return["alert_img"] = print_status_image (STATUS_ALERT_FIRED, __('Alert fired'), true);	
	}
	
	return $return;
}

/** 
 * This function is used once, in reporting_viewer.php, the HTML report render
 * file. This function proccess each report item and write the render in the
 * table record.
 * 
 * @param array $content Record of treport_content table for current item
 * @param array $table HTML Table row
 * @param array $report Report contents, with some added fields.
 * @param array $mini Mini flag for reduce the size.
 * 
 */

function render_report_html_item ($content, $table, $report, $mini = false) {
    global $config;
		
	if($mini){
		$sizh = '';
		$sizhfin = '';
		$sizem = '1.5';
		$sizgraph_w = '350';
		$sizgraph_h = '100';
	}
	else{
		$sizh = '<h4>';
		$sizhfin = '</h4>';
		$sizem = '3';
		$sizgraph_w = '750';
		$sizgraph_h = '230';
	}

		
	$module_name = get_db_value ('nombre', 'tagente_modulo', 'id_agente_modulo', $content['id_agent_module']);
	if ($content['id_agent_module'] != 0) {
		$agent_name = get_agentmodule_agent_name ($content['id_agent_module']);
	}
	else {
		$agent_name = get_agent_name($content['id_agent']);
	}

	switch ($content["type"]) {
		case 1:
		case 'simple_graph':
			//RUNNING
			$table->colspan[1][0] = 4;
			$data = array ();
			$data[0] = $sizh.__('Simple graph').$sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			$data[2] = $sizh.human_time_description ($content['period']).$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[2][0] = 4;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$data[0] = '<img src="include/fgraph.php?tipo=sparse&id='.$content['id_agent_module'].'&height='.$sizgraph_h.'&width='.$sizgraph_w.'&period='.$content['period'].'&date='.$report["datetime"].'&avg_only=1&pure=1" border="0" alt="">';
			array_push ($table->data, $data);
			
			break;

		case 2:
		case 'custom_graph':
			//RUNNING
			$graph = get_db_row ("tgraph", "id_graph", $content['id_gs']);
			$data = array ();
			$data[0] = $sizh.__('Custom graph').$sizhfin;
			$data[1] = $sizh.$graph['name'].$sizhfin;
			$data[2] = $sizh.human_time_description ($content['period']).$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$result = get_db_all_rows_field_filter ("tgraph_source", "id_graph", $content['id_gs']);
			$modules = array ();
			$weights = array ();
			if ($result === false)
				$result = array();
			
			foreach ($result as $content2) {
				array_push ($modules, $content2['id_agent_module']);
				array_push ($weights, $content2["weight"]);
			}
			
			$graph_width = get_db_sql ("SELECT width FROM tgraph WHERE id_graph = ".$content["id_gs"]);
			$graph_height= get_db_sql ("SELECT height FROM tgraph WHERE id_graph = ".$content["id_gs"]);
	
	
			$table->colspan[2][0] = 3;
			$data = array ();
			$data[0] = '<img src="include/fgraph.php?tipo=combined&id='.implode (',', $modules).'&weight_l='.implode (',', $weights).'&height='.$sizgraph_h.'&width='.$sizgraph_w.'&period='.$content['period'].'&date='.$report["datetime"].'&stacked='.$graph["stacked"].'&pure=1" border="1" alt="">';
			array_push ($table->data, $data);
	
			break;
		case 3:
		case 'SLA':
			//RUNNING
			$table->style[1] = 'text-align: right';
			$data = array ();
			$data[0] = $sizh.__('S.L.A.').$sizhfin;
			$data[1] = $sizh.human_time_description ($content['period']).$sizhfin;;
			$n = array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$slas = get_db_all_rows_field_filter ('treport_content_sla_combined',
								'id_report_content', $content['id_rc']);
			if ($slas === false) {
				$data = array ();
				$table->colspan[2][0] = 3;
				$data[0] = __('There are no SLAs defined');
				array_push ($table->data, $data);
				$slas = array ();
			}
			
			$sla_failed = false;
			foreach ($slas as $sla) {
				$data = array ();
				
				$data[0] = '<strong>'.__('Agent')."</strong> : ";
				$data[0] .= get_agentmodule_agent_name ($sla['id_agent_module'])."<br />";
				$data[0] .= '<strong>'.__('Module')."</strong> : ";
				$data[0] .= get_agentmodule_name ($sla['id_agent_module'])."<br />";
				$data[0] .= '<strong>'.__('SLA Max. (value)')."</strong> : ";
				$data[0] .= $sla['sla_max']."<br />";
				$data[0] .= '<strong>'.__('SLA Min. (value)')."</strong> : ";
				$data[0] .= $sla['sla_min']."<br />";
				$data[0] .= '<strong>'.__('SLA Limit')."</strong> : ";
				$data[0] .= $sla['sla_limit'];
				$sla_value = get_agentmodule_sla ($sla['id_agent_module'], $content['period'],
					$sla['sla_min'], $sla['sla_max'], $report["datetime"]);
				if ($sla_value === false) {
					$data[1] = '<span style="font: bold '.$sizem.'em Arial, Sans-serif; color: #0000FF;">';
					$data[1] .= __('Unknown');
				} else {
					if ($sla_value >= $sla['sla_limit'])
						$data[1] = '<span style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">';
					else {
						$sla_failed = true;
						$data[1] = '<span style="font: bold '.$sizem.'em Arial, Sans-serif; color: #ff0000;">';
					}
					$data[1] .= format_numeric ($sla_value). " %";
				}
				$data[1] .= "</span>";
				
				$n = array_push ($table->data, $data);
			}
			if (!empty ($slas)) {
				$data = array ();
				if ($sla_failed == false)
					$data[0] = '<span style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">'.__('OK').'</span>';
				else
					$data[0] = '<span style="font: bold '.$sizem.'em Arial, Sans-serif; color: #ff0000;">'.__('Fail').'</span>';
				$n = array_push ($table->data, $data);
				$table->colspan[$n - 1][0] = 3;
				$table->rowstyle[$n - 1] = 'text-align: right';
			}
			
			break;
		case 6:
		case 'monitor_report':
			//RUNNING
			$data = array ();
			$data[0] = $sizh.__('Monitor report').$sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			$data[2] = $sizh.human_time_description ($content['period']).$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$monitor_value = get_agentmodule_sla ($content['id_agent_module'], $content['period'], 1, false, $report["datetime"]);
			if ($monitor_value === false) {
				$monitor_value = __('Unknown');
			} else {
				$monitor_value = format_numeric ($monitor_value);
			}
			$data[0] = '<p style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">';
			$data[0] .= $monitor_value.' % <img src="images/b_green.png" height="32" width="32" /></p>';
			if ($monitor_value !== __('Unknown')) {
				$monitor_value = format_numeric (100 - $monitor_value, 2) ;
			}
			$data[1] = '<p style="font: bold '.$sizem.'em Arial, Sans-serif; color: #ff0000;">';
			$data[1] .= $monitor_value.' % <img src="images/b_red.png" height="32" width="32" /></p>';
			array_push ($table->data, $data);
			
			break;
		case 7:
		case 'avg_value':
			//RUNNING
			$data = array ();
			$data[0] = $sizh.__('Avg. Value').$sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			$data[2] = $sizh.human_time_description ($content['period']).$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$value = get_agentmodule_data_average ($content['id_agent_module'], $content['period'], $report["datetime"]);
			if ($value === false) {
				$value = __('Unknown');
			} else {
				$value = format_numeric ($value);
			}
			$data[0] = '<p style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">'.$value.'</p>';
			array_push ($table->data, $data);
			
			break;
		case 8:
		case 'max_value':
			//RUNNING
			$data = array ();
			$data[0] = $sizh.__('Max. Value').$sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			$data[2] = $sizh.human_time_description ($content['period']).$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$value = format_numeric (get_agentmodule_data_max ($content['id_agent_module'], $content['period'], $report["datetime"]));
			$data[0] = '<p style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">'.$value.'</p>';
			array_push ($table->data, $data);
			
			break;
		case 9:
		case 'min_value':
			//RUNNING
			$data = array ();
			$data[0] = $sizh.__('Min. Value').$sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			$data[2] = $sizh.human_time_description ($content['period']).$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[0][0] = 2;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[1][0] = 2;
			$value = get_agentmodule_data_min ($content['id_agent_module'], $content['period'], $report["datetime"]);
			if ($value === false) {
				$value = __('Unknown');
			} else {
				$value = format_numeric ($value);
			}
			$data[0] = '<p style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">'.$value.'</p>';
			array_push ($table->data, $data);
			
			break;
		case 10:
		case 'sumatory':
			//RUNNING
			$data = array ();
			$data[0] = $sizh.__('Sumatory').$sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			$data[2] = $sizh.human_time_description ($content['period']).$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[0][0] = 2;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[1][0] = 2;
			$value = get_agentmodule_data_sum ($content['id_agent_module'], $content['period'], $report["datetime"]);
			if ($value === false) {
				$value = __('Unknown');
			} else {
				$value = format_numeric ($value);
			}

			$data[0] = '<p style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">'.$value.'</p>';
			array_push ($table->data, $data);
			
			break;
		case 'agent_detailed_event':
		case 'event_report_agent':
			//RUNNING
			$data = array ();
			$data[0] = $sizh.__('Agent detailed event').$sizhfin;
			$data[1] = $sizh.get_agent_name($content['id_agent']).$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$data[0] = get_agents_detailed_event_reporting ($content['id_agent'], $content['period'], $report["datetime"], true);
			array_push ($table->data, $data);
			break;
		case 'text':
			$data = array();
			$data[0] = $sizh. __('Text') . $sizhfin;
			array_push ($table->data, $data);
			$table->colspan[0][0] = 2;
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[0][0] = 2;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			$data[0] = html_entity_decode($content['text']);
			array_push($table->data, $data);
			$table->colspan[2][0] = 2;
			break;
		case 'sql':
			$data = array();
			$data[0] = $sizh. __('SQL') . $sizhfin;
			array_push ($table->data, $data);
			$table->colspan[0][0] = 2;
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[0][0] = 2;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$table2->class = 'databox';
			$table2->width = '100%';
			
			//Create the head
			$table2->head = array();
			if ($content['header_definition'] != '') {
				$table2->head = explode('|', $content['header_definition']);
			}
			
			if ($content['treport_custom_sql_id'] != 0) {
				$sql = safe_output (get_db_value_filter('`sql`', 'treport_custom_sql', array('id' => $content['treport_custom_sql_id'])));
			}
			else {
				$sql = safe_output ($content['external_source']);
			}

			// Minimal security check on SQL incoming from the database	
			$sql = check_sql ($sql);
echo "$sql output";	
			$result = get_db_all_rows_sql($sql);

			if ($result === false) {
				$result = array();
			}
		
			if (isset($result[0])) {
				if (count($result[0]) > count($table2->head)) {
					$table2->head = array_pad($table2->head, count($result[0]), '&nbsp;');
				}
			}
			
			$table2->data = array();
			foreach ($result as $row) {
				array_push($table2->data, $row);
			}
			
			$cellContent = print_table($table2, true);
			array_push($table->data, array($cellContent));
			break;
		case 'event_report_group':
			$data = array ();
			$data[0] = $sizh .  __('Group detailed event') . $sizhfin;
			$data[1] = $sizh . get_group_name($content['id_agent']) . $sizhfin;
			array_push ($table->data, $data);
			
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$data[0] = get_group_detailed_event_reporting($content['id_agent'], $content['period'], $report["datetime"], true);
			array_push ($table->data, $data);
			break;
		case 'event_report_module':
			$data = array ();
			$data[0] = $sizh. __('Module detailed event') . $sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$data[0] = get_module_detailed_event_reporting($content['id_agent_module'], $content['period'], $report["datetime"], true);
			array_push ($table->data, $data);
			break;
		case 'alert_report_module':
			$data = array ();
			$data[0] = $sizh. __('Alert report module') . $sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$data[0] = alert_reporting_module ($content['id_agent_module'], $content['period'], $report["datetime"], true);
			array_push ($table->data, $data);
			break; 
		case 'alert_report_agent':
			$data = array ();
			$data[0] = $sizh. __('Alert report agent') . $sizhfin;
			$data[1] = $sizh.$agent_name.$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$data[0] = alert_reporting_agent ($content['id_agent'], $content['period'], $report["datetime"], true);
			array_push ($table->data, $data);
			break;
		case 'url':
			$table->colspan[1][0] = 2;
			$data = array ();
			$data[0] = $sizh. __('Import text from URL') . $sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array();
			$table->colspan[2][0] = 3;
			$data[0] = '<iframe id="item_' . $content['id_rc'] . '" src ="' . $content["external_source"] . '" width="100%" height="100%"></iframe>';
			$data[0] .= '<script>
				$(document).ready (function () {
					$("#item_' . $content['id_rc'] . '").height($(document.body).height() + 0);
			});</script>';
			
			array_push ($table->data, $data);
			break;
		case 'database_serialized':
			$data = array ();
			$data[0] = $sizh. __('Serialize data') . $sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$table2->class = 'databox';
			$table2->width = '100%';
			
			//Create the head
			$table2->head = array();
			if ($content['header_definition'] != '') {
				$table2->head = explode('|', $content['header_definition']);
			}
			array_unshift($table2->head, __('Date'));
			
			$datelimit = $report["datetime"] - $content['period'];
			
			$result = get_db_all_rows_sql('SELECT *
				FROM tagente_datos_string
				WHERE id_agente_modulo = ' . $content['id_agent_module'] . '
				AND utimestamp > ' . $datelimit . ' AND utimestamp <= ' . $report["datetime"]);
			if ($result === false) {
				$result = array();
			}
			
			$table2->data = array();
			foreach ($result as $row) {
				$date = date ($config["date_format"], $row['utimestamp']);
				$serialized = $row['datos'];
				$rowsUnserialize = explode($content['line_separator'], $serialized);
				foreach ($rowsUnserialize as $rowUnser) {
					$columnsUnserialize = explode($content['column_separator'], $rowUnser);
					array_unshift($columnsUnserialize, $date);
					array_push($table2->data, $columnsUnserialize);
				} 
			}
			
			$cellContent = print_table($table2, true);
			array_push($table->data, array($cellContent));
			$table->colspan[1][0] = 2;
			break;
		case 'TTRT':
			$data = array ();
			$data[0] = $sizh. __('TTRT') . $sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$ttr = get_agentmodule_ttr ($content['id_agent_module'], $content['period'], $report["datetime"]);
			if ($ttr === false) {
				$ttr = __('Unknown');
			} else if ($ttr != 0) {
				$ttr = human_time_description_raw ($ttr);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$data[0] = '<p style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">'.$ttr.'</p>';
			array_push ($table->data, $data);
			break;
		case 'TTO':
			$data = array ();
			$data[0] = $sizh. __('TTO') . $sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$tto = get_agentmodule_tto ($content['id_agent_module'], $content['period'], $report["datetime"]);
			if ($tto === false) {
				$tto = __('Unknown');
			} else if ($tto != 0) {
				$tto = human_time_description_raw ($tto);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$data[0] = '<p style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">'.$tto.'</p>';
			array_push ($table->data, $data);
			break;
		case 'MTBF':
			$data = array ();
			$data[0] = $sizh. __('MTBF') . $sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$mtbf = get_agentmodule_mtbf ($content['id_agent_module'], $content['period'], $report["datetime"]);
			if ($mtbf === false) {
				$mtbf = __('Unknown');
			} else if ($mtbf != 0) {
				$mtbf = human_time_description_raw ($mtbf);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$data[0] = '<p style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">'.$mtbf.'</p>';
			array_push ($table->data, $data);
			break;
		case 'MTTR':
			$data = array ();
			$data[0] = $sizh. __('MTTR') . $sizhfin;
			$data[1] = $sizh.$agent_name.' - '.$module_name.$sizhfin;
			array_push ($table->data, $data);
			
			// Put description at the end of the module (if exists)
			if ($content["description"] != ""){
				$table->colspan[1][0] = 3;
				$data_desc = array();
				$data_desc[0] = $content["description"];
				array_push ($table->data, $data_desc);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$mttr = get_agentmodule_mttr ($content['id_agent_module'], $content['period'], $report["datetime"]);
			if ($mttr === false) {
				$mttr = __('Unknown');
			} else if ($mttr != 0) {
				$mttr = human_time_description_raw ($mttr);
			}
			
			$data = array ();
			$table->colspan[2][0] = 3;
			$data[0] = '<p style="font: bold '.$sizem.'em Arial, Sans-serif; color: #000000;">'.$mttr.'</p>';
			array_push ($table->data, $data);
			break;
	}
}

/** 
 * Get the MTBF value of an agent module in a period of time. See
 * http://en.wikipedia.org/wiki/Mean_time_between_failures
 * 
 * @param int Agent module id
 * @param int Period of time to check (in seconds)
 * @param int Top date to check the values. Default current time.
 * 
 * @return float The MTBF value in the interval.
 */
function get_agentmodule_mtbf ($id_agent_module, $period, $date = 0) {

	// Initialize variables
	if (empty ($date)) $date = get_system_time ();
	if ((empty ($period)) OR ($period == 0)) $period = $config["sla_period"];
	
	// Read module configuration
	$datelimit = $date - $period;	
	$module = get_db_row_sql ('SELECT max_critical, min_critical, id_tipo_modulo
	                           FROM tagente_modulo
	                           WHERE id_agente_modulo = ' . (int) $id_agent_module);
	if ($module === false) {
		return false;
	}

	$critical_min = $module['min_critical'];
	$critical_max = $module['max_critical'];
	$module_type = $module['id_tipo_modulo'];
	
	// Set critical_min and critical for proc modules
	$module_type_str = get_module_type_name ($module_type);
	if (strstr ($module_type_str, 'proc') !== false &&
	    ($critical_min == 0 && $critical_max == 0)) {
		$critical_min = 1;
	}
	
	// Get module data
	$interval_data = get_db_all_rows_sql ('SELECT * FROM tagente_datos 
			WHERE id_agente_modulo = ' . (int) $id_agent_module .
			' AND utimestamp > ' . (int) $datelimit .
			' AND utimestamp < ' . (int) $date .
			' ORDER BY utimestamp ASC', true);
	if ($interval_data === false) $interval_data = array ();

	// Get previous data
	$previous_data = get_previous_data ($id_agent_module, $datelimit);
	if ($previous_data !== false) {
		$previous_data['utimestamp'] = $datelimit;
		array_unshift ($interval_data, $previous_data);
	}

	// Get next data
	$next_data = get_next_data ($id_agent_module, $date);
	if ($next_data !== false) {
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	} else if (count ($interval_data) > 0) {
		// Propagate the last known data to the end of the interval
		$next_data = array_pop ($interval_data);
		array_push ($interval_data, $next_data);
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	}

	if (count ($interval_data) < 2) {
		return false;
	}

	// Set initial conditions
	$critical_period = 0;
	$first_data = array_shift ($interval_data);
	$previous_utimestamp = $first_data['utimestamp'];
	if ((($critical_max > $critical_min AND ($first_data['datos'] > $critical_max OR  $first_data['datos'] < $critical_min))) OR
		     ($critical_max <= $critical_min AND $first_data['datos'] < $critical_min)) {
		$previous_status = 1;
		$critical_count = 1;
	} else {
		$previous_status = 0;
		$critical_count = 0;
	}

	foreach ($interval_data as $data) {
		// Previous status was critical
		if ($previous_status == 1) {
			$critical_period += $data['utimestamp'] - $previous_utimestamp;
		}
		
		// Re-calculate previous status for the next data
		if ((($critical_max > $critical_min AND ($data['datos'] > $critical_max OR  $data['datos'] < $critical_min))) OR
		     ($critical_max <= $critical_min AND $data['datos'] < $critical_min)) {
			if ($previous_status == 0) {
				$critical_count++;
			}
			$previous_status = 1;
		} else {
			$previous_status = 0;
		}
		
		$previous_utimestamp = $data['utimestamp'];
	}

	if ($critical_count == 0) {
		return 0;
	}

	return ($period - $critical_period) / $critical_count;
}

/** 
 * Get the MTTR value of an agent module in a period of time. See
 * http://en.wikipedia.org/wiki/Mean_time_to_recovery
 * 
 * @param int Agent module id
 * @param int Period of time to check (in seconds)
 * @param int Top date to check the values. Default current time.
 * 
 * @return float The MTTR value in the interval.
 */
function get_agentmodule_mttr ($id_agent_module, $period, $date = 0) {

	// Initialize variables
	if (empty ($date)) $date = get_system_time ();
	if ((empty ($period)) OR ($period == 0)) $period = $config["sla_period"];
	
	// Read module configuration
	$datelimit = $date - $period;	
	$module = get_db_row_sql ('SELECT max_critical, min_critical, id_tipo_modulo
	                           FROM tagente_modulo
	                           WHERE id_agente_modulo = ' . (int) $id_agent_module);
	if ($module === false) {
		return false;
	}

	$critical_min = $module['min_critical'];
	$critical_max = $module['max_critical'];
	$module_type = $module['id_tipo_modulo'];
	
	// Set critical_min and critical for proc modules
	$module_type_str = get_module_type_name ($module_type);
	if (strstr ($module_type_str, 'proc') !== false &&
	    ($critical_min == 0 && $critical_max == 0)) {
		$critical_min = 1;
	}
	
	// Get module data
	$interval_data = get_db_all_rows_sql ('SELECT * FROM tagente_datos 
			WHERE id_agente_modulo = ' . (int) $id_agent_module .
			' AND utimestamp > ' . (int) $datelimit .
			' AND utimestamp < ' . (int) $date .
			' ORDER BY utimestamp ASC', true);
	if ($interval_data === false) $interval_data = array ();

	// Get previous data
	$previous_data = get_previous_data ($id_agent_module, $datelimit);
	if ($previous_data !== false) {
		$previous_data['utimestamp'] = $datelimit;
		array_unshift ($interval_data, $previous_data);
	}

	// Get next data
	$next_data = get_next_data ($id_agent_module, $date);
	if ($next_data !== false) {
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	} else if (count ($interval_data) > 0) {
		// Propagate the last known data to the end of the interval
		$next_data = array_pop ($interval_data);
		array_push ($interval_data, $next_data);
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	}

	if (count ($interval_data) < 2) {
		return false;
	}

	// Set initial conditions
	$critical_period = 0;
	$first_data = array_shift ($interval_data);
	$previous_utimestamp = $first_data['utimestamp'];
	if ((($critical_max > $critical_min AND ($first_data['datos'] > $critical_max OR  $first_data['datos'] < $critical_min))) OR
		     ($critical_max <= $critical_min AND $first_data['datos'] < $critical_min)) {
		$previous_status = 1;
		$critical_count = 1;
	} else {
		$previous_status = 0;
		$critical_count = 0;
	}

	foreach ($interval_data as $data) {
		// Previous status was critical
		if ($previous_status == 1) {
			$critical_period += $data['utimestamp'] - $previous_utimestamp;
		}
		
		// Re-calculate previous status for the next data
		if ((($critical_max > $critical_min AND ($data['datos'] > $critical_max OR  $data['datos'] < $critical_min))) OR
		     ($critical_max <= $critical_min AND $data['datos'] < $critical_min)) {
			if ($previous_status == 0) {
				$critical_count++;
			}
			$previous_status = 1;
		} else {
			$previous_status = 0;
		}
		
		$previous_utimestamp = $data['utimestamp'];
	}

	if ($critical_count == 0) {
		return 0;
	}

	return $critical_period / $critical_count;
}

/** 
 * Get the TTO value of an agent module in a period of time.
 * 
 * @param int Agent module id
 * @param int Period of time to check (in seconds)
 * @param int Top date to check the values. Default current time.
 * 
 * @return float The TTO value in the interval.
 */
function get_agentmodule_tto ($id_agent_module, $period, $date = 0) {

	// Initialize variables
	if (empty ($date)) $date = get_system_time ();
	if ((empty ($period)) OR ($period == 0)) $period = $config["sla_period"];
	
	// Read module configuration
	$datelimit = $date - $period;	
	$module = get_db_row_sql ('SELECT max_critical, min_critical, id_tipo_modulo
	                           FROM tagente_modulo
	                           WHERE id_agente_modulo = ' . (int) $id_agent_module);
	if ($module === false) {
		return false;
	}

	$critical_min = $module['min_critical'];
	$critical_max = $module['max_critical'];
	$module_type = $module['id_tipo_modulo'];
	
	// Set critical_min and critical for proc modules
	$module_type_str = get_module_type_name ($module_type);
	if (strstr ($module_type_str, 'proc') !== false &&
	    ($critical_min == 0 && $critical_max == 0)) {
		$critical_min = 1;
	}

	// Get module data
	$interval_data = get_db_all_rows_sql ('SELECT * FROM tagente_datos 
			WHERE id_agente_modulo = ' . (int) $id_agent_module .
			' AND utimestamp > ' . (int) $datelimit .
			' AND utimestamp < ' . (int) $date .
			' ORDER BY utimestamp ASC', true);
	if ($interval_data === false) $interval_data = array ();

	// Get previous data
	$previous_data = get_previous_data ($id_agent_module, $datelimit);
	if ($previous_data !== false) {
		$previous_data['utimestamp'] = $datelimit;
		array_unshift ($interval_data, $previous_data);
	}

	// Get next data
	$next_data = get_next_data ($id_agent_module, $date);
	if ($next_data !== false) {
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	} else if (count ($interval_data) > 0) {
		// Propagate the last known data to the end of the interval
		$next_data = array_pop ($interval_data);
		array_push ($interval_data, $next_data);
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	}

	if (count ($interval_data) < 2) {
		return false;
	}

	// Set initial conditions
	$critical_period = 0;
	$first_data = array_shift ($interval_data);
	$previous_utimestamp = $first_data['utimestamp'];
	if ((($critical_max > $critical_min AND ($first_data['datos'] > $critical_max OR  $first_data['datos'] < $critical_min))) OR
		     ($critical_max <= $critical_min AND $first_data['datos'] < $critical_min)) {
		$previous_status = 1;	
	} else {
		$previous_status = 0;
	}

	foreach ($interval_data as $data) {
		// Previous status was critical
		if ($previous_status == 1) {
			$critical_period += $data['utimestamp'] - $previous_utimestamp;
		}
		
		// Re-calculate previous status for the next data
		if ((($critical_max > $critical_min AND ($data['datos'] > $critical_max OR  $data['datos'] < $critical_min))) OR
		     ($critical_max <= $critical_min AND $data['datos'] < $critical_min)) {
			$previous_status = 1;
		} else {
			$previous_status = 0;
		}
		
		$previous_utimestamp = $data['utimestamp'];
	}

	return $period - $critical_period;
}

/** 
 * Get the TTR value of an agent module in a period of time.
 * 
 * @param int Agent module id
 * @param int Period of time to check (in seconds)
 * @param int Top date to check the values. Default current time.
 * 
 * @return float The TTR value in the interval.
 */
function get_agentmodule_ttr ($id_agent_module, $period, $date = 0) {

	// Initialize variables
	if (empty ($date)) $date = get_system_time ();
	if ((empty ($period)) OR ($period == 0)) $period = $config["sla_period"];
	
	// Read module configuration
	$datelimit = $date - $period;	
	$module = get_db_row_sql ('SELECT max_critical, min_critical, id_tipo_modulo
	                           FROM tagente_modulo
	                           WHERE id_agente_modulo = ' . (int) $id_agent_module);
	if ($module === false) {
		return false;
	}

	$critical_min = $module['min_critical'];
	$critical_max = $module['max_critical'];
	$module_type = $module['id_tipo_modulo'];
	
	// Set critical_min and critical for proc modules
	$module_type_str = get_module_type_name ($module_type);
	if (strstr ($module_type_str, 'proc') !== false &&
	    ($critical_min == 0 && $critical_max == 0)) {
		$critical_min = 1;
	}
	
	// Get module data
	$interval_data = get_db_all_rows_sql ('SELECT * FROM tagente_datos 
			WHERE id_agente_modulo = ' . (int) $id_agent_module .
			' AND utimestamp > ' . (int) $datelimit .
			' AND utimestamp < ' . (int) $date .
			' ORDER BY utimestamp ASC', true);
	if ($interval_data === false) $interval_data = array ();

	// Get previous data
	$previous_data = get_previous_data ($id_agent_module, $datelimit);
	if ($previous_data !== false) {
		$previous_data['utimestamp'] = $datelimit;
		array_unshift ($interval_data, $previous_data);
	}

	// Get next data
	$next_data = get_next_data ($id_agent_module, $date);
	if ($next_data !== false) {
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	} else if (count ($interval_data) > 0) {
		// Propagate the last known data to the end of the interval
		$next_data = array_pop ($interval_data);
		array_push ($interval_data, $next_data);
		$next_data['utimestamp'] = $date;
		array_push ($interval_data, $next_data);
	}

	if (count ($interval_data) < 2) {
		return false;
	}

	// Set initial conditions
	$critical_period = 0;
	$first_data = array_shift ($interval_data);
	$previous_utimestamp = $first_data['utimestamp'];
	if ((($critical_max > $critical_min AND ($first_data['datos'] > $critical_max OR  $first_data['datos'] < $critical_min))) OR
		     ($critical_max <= $critical_min AND $first_data['datos'] < $critical_min)) {
		$previous_status = 1;	
	} else {
		$previous_status = 0;
	}

	foreach ($interval_data as $data) {
		// Previous status was critical
		if ($previous_status == 1) {
			$critical_period += $data['utimestamp'] - $previous_utimestamp;
		}
		
		// Re-calculate previous status for the next data
		if ((($critical_max > $critical_min AND ($data['datos'] > $critical_max OR  $data['datos'] < $critical_min))) OR
		     ($critical_max <= $critical_min AND $data['datos'] < $critical_min)) {
			$previous_status = 1;
		} else {
			$previous_status = 0;
		}
		
		$previous_utimestamp = $data['utimestamp'];
	}

	return $critical_period;
}

?>
