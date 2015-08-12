<?
session_start();
$db = new SQLite3(__DIR__ . "/../db/main.db");

$schema = [
  'stations' => [
    'id'          => 'INTEGER PRIMARY KEY', 
    
    // FCC callsign or some other unique reference
    'callsign'    => 'TEXT',

    // an integer in megahertz * 100, such as 8990 or 9070 ... this matches the port usually.
    'frequenty'   => 'INTEGER DEFAULT 0',
    'description' => 'TEXT',
    'base_url'    => 'TEXT',
    'last_seen'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'first_seen'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'pings'       => 'INTEGER DEFAULT 0',
    'drops'       => 'INTEGER DEFAULT 0',
    'latency'     => 'INTEGER DEFAULT 0',
    'active'      => 'INTEGER DEFAULT 1',

    // Where the station is
    'lat'         => 'DOUBLE default 0',
    'long'        => 'DOUBLE default 0',

    'log'         => 'TEXT',
    'notes'       => 'TEXT'
  ],

  // The reminders table is to email someone
  // when they set a reminder for a particular station.
  'reminders' => [
    'id'          => 'INTEGER PRIMARY KEY', 

    'start_time'  => 'TIMESTAMP',
    'end_time'    => 'TIMESTAMP',

    'station'    => 'TEXT',
    'email'       => 'TEXT',

    'notes'       => 'TEXT'
  ]
];

function sanitize($list) {
  $ret = [];

  foreach($list as $key) {
    if(isset($_REQUEST[$key])) {
      $ret[$key] = SQLite3::escapeString($_REQUEST[$key]);
    } else {
      $ret[$key] = false;
    }
  }

  return $ret;
}

function is_read_only() {
  return empty($_SESSION['admin']) || $_SESSION['admin'] != 1;
}

function db_all($what) {
  $res = [];
  while($item = prune($what)) {
    $res[] = $item;
  }
  return $res;
}

function db_get($str) {
  global $db;
  $res = $db->query($str);
  if($res) {
    return $res->fetchArray();
  }
}


function prune($obj) {
  $ret = $obj->fetchArray();
  if($ret) {
    foreach(array_keys($ret) as $key) {
      if(strval(intval($key)) == $key) {
        unset($ret[$key]);
      }
    }
  } 
  return $ret;
}

function sql_escape_hash($obj) {
  $res = [];
  foreach($obj as $key => $value) {
    $res[SQLite3::escapeString($key)] = SQLite3::escapeString($value);
  }
  return $res;
}

function sql_kv($hash, $operator = '=', $quotes = "'") {
  $ret = [];
  foreach($hash as $key => $value) {
    if ( !empty($value) ) {
      $ret[] = "$key $operator $quotes$value$quotes";
    }
  } 
  return $ret;
}

// active stations are things we've seen in the past few days
function active_stations() {
  global $db;
  return db_all($db->query('select * from stations where active = 1 order by callsign asc'));
}

function get_station($dirty) {
  $clean = sql_escape_hash($dirty);
  $inj = sql_kv($clean);
  return db_get('select * from stations where ' . implode(' and ', $inj));
}

function del_station($dirty) {
  if (is_read_only()) {
    return false;
  }

  global $db;
  $clean = sql_escape_hash($dirty);
  $inj = sql_kv($dirty);
  return $db->exec('update stations set active = 0 where ' . implode(' and ', $inj));
}

function add_station($dirty) {
  if (is_read_only()) {
    return false;
  }

  global $db;
  $clean = sql_escape_hash($dirty);

  $station = db_get('select * from stations where callsign = "' . $clean['callsign'] . '"');
  if(!$station) {
    $lhs = array_keys($dirty); $rhs = array_values($dirty);
    return $db->exec('insert into stations (' . implode(',', $lhs) . ') values ("' . implode('","', $dirty) . '")');
  } else {
    $inj = sql_kv($dirty);
    return $db->exec('update stations set ' . implode(',', $inj) . ' where id = ' . $station['id']);
  }
}

// Return all the call-signs ordered by the long/lat using a simple 
// euclidean distance
function order_stations_by_distance($long, $lat) {
}

// Looks for a user based on their ip address and the geoip lookup database,
// returning their longitude and latitude
function where_am_i() {
  $addr = $_SERVER['REMOTE_ADDR'];
  if ($addr == '127.0.0.1') { 
    // we'll just use this for testing... it was taken from a coffee shop
    // in "UNINCORPORATED LOS ANGELES" --- also known as Palms.
    $addr = '50.1.134.134';
  }
  $addr = escapeshellarg($addr);

  $res = shell_exec ("/usr/bin/geoiplookup -f scripts/GeoLiteCity.dat $addr");
  $parts = explode(',', $res);
  
  // This means we failed to find it
  if (trim($parts[2]) == 'N/A') {
    // return a null type
    return Null;
  }

  // Otherwise we have a fairly decent regional 
  // idea of where this person is.
  return [$parts[5], $parts[6]];
}

