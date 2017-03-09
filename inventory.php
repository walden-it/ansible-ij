#!/usr/bin/php
<?php
/**
 * @file
 * Example custom dynamic inventory script for Ansible, in PHP.
 */
/**
 * Example inventory for testing.
 *
 * @return array
 *   An example inventory with two hosts.
 */


function dbconn()
{
global $pdo;
$host = '127.0.0.1';

$db   = 'ansible';
$user = 'inventory';
$pass = '';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);
return($pdo);
}

$db = dbconn();
function get_groups()
{
global $db;
$groupsQuery= "SELECT * FROM groups";
$query = $db->query($groupsQuery);

$groups = $query->fetchAll();
return($groups);


}

function get_vars($host_id,$ipaddr)
{
	global $db,$hostvars;
        $vars = array();
	$varsQuery = "SELECT * FROM vars WHERE host_id=".$host_id;
        $query = $db->query($varsQuery);
	while($var = $query->fetch())
        $hostvars[$ipaddr][$var['key']] = $var['value'];


}

function get_hosts($group_id)
{
 global $db,$hostvars;
 $hostsQuery = "SELECT * FROM hosts WHERE enabled=1 AND group_id=".$group_id;
 $query = $db->query($hostsQuery);
 while($host = $query->fetch())
 {
 	$hosts[] = $host['ipaddr'];
	get_vars($host['id'],$host['ipaddr']);
 }

return($hosts);
}



function inventory() {
global $db, $hostvars;
$groups = get_groups();

foreach($groups as $group)
{

 $ret[$group['name']] = array("hosts" => get_hosts($group['id']));
}

$ret["_meta"]["hostvars"] = $hostvars;
print(json_encode($ret));
}
/**
 * Get inventory.
 *
 * @param array $argv
 *   Array of command line arguments (as in $_SERVER['argv']).
 *
 * @return array
 *   Inventory of groups or vars, depending on arguments.
 */

  if (!empty($argv[1]) && $argv[1] == '--list') {
    $inventory = inventory();
  }

