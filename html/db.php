<?
$db = new SQLite3("main.db");

$schema = [
  'id'          => 'INTEGER PRIMARY KEY', 
  'callsign'    => 'TEXT',
  'description' => 'TEXT',
  'base_url'    => 'TEXT',
  'last_seen'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
  'first_seen'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
  'pings'       => 'INTEGER DEFAULT 0',
  'drops'       => 'INTEGER DEFAULT 0',
  'latency'     => 'INTEGER DEFAULT 0',
  'active'      => 'INTEGER DEFAULT 1',
  'log'         => 'TEXT',
  'notes'       => 'TEXT'
];

function is_read_only() {
  return ($_SERVER['REMOTE_ADDR'] !== '::1');
}

function db_all($what) {
  $res = [];
  while($res[] = prune($what));
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
  return db_all($db->query('select * from stations where active = 1'));
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

