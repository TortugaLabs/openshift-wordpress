<?php

$classes = [];
foreach (['plugin','theme','language'] as $i) {
  $classes[$i] = $i;
}

$manifest = getenv('MANIFEST');
if ($manifest === FALSE) $manifest = 'wp-manifest.txt';
$data_dir = getenv('OPENSHIFT_DATA_DIR');
if ($data_dir === FALSE) die("Must define OPENSHIFT_DATA_DIR env variable\n");

function cunlink($f) {
  if (is_file($f)) return unlink($f);
  return TRUE;
}
function get_url($class,$name) {
  $ch = curl_init(); 
  $timeout = 5; // set to zero for no timeout 
  curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
  switch ($class) {
    case "plugin":
      curl_setopt($ch, CURLOPT_URL, 'https://api.wordpress.org/plugins/info/1.0/'.$name);
      $file_contents = curl_exec($ch); 
      curl_close($ch); 
      $dat = unserialize($file_contents);
      break;
    case "theme":
      curl_setopt($ch, CURLOPT_URL, 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]='.$name);
      $file_contents = curl_exec($ch); 
      curl_close($ch); 
      $dat = json_decode($file_contents);
      break;
    case 'language':
      curl_setopt($ch, CURLOPT_URL, 'https://api.wordpress.org/translations/core/1.0/');
      $file_contents = curl_exec($ch); 
      curl_close($ch); 
      $dat = json_decode($file_contents);
      if ($dat == NULL) return FALSE;
      if (!isset($dat->translations)) return FALSE;
      foreach ($dat->translations as $lang) {
        if ($lang->language == $name) return $lang->package;
      }
      return FALSE;
  }
  if ($dat == NULL) return FALSE;
  if (!isset($dat->download_link)) return FALSE;
  return $dat->download_link;
}
function get_github_url($class,$name,$ghrepo) {
  $toks = preg_split('/[,\/]/',$ghrepo);
  if (count($toks) < 2 || count($toks) > 3) return FALSE;
  $owner = $toks[0];
  $repo = $toks[1];
  $version = count($toks) == 3 ? 'tags/'.$toks[2] : 'latest';
  
  $ch = curl_init(); 
  $timeout = 5; // set to zero for no timeout 
  curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/$owner/$repo/releases/$version");
  curl_setopt($ch,CURLOPT_USERAGENT,'iliu-net/openshift-wordpress');

  $file_contents = curl_exec($ch); 
  curl_close($ch); 
  $dat = json_decode($file_contents);
  if ($dat == NULL) return FALSE;
  if (!isset($dat->assets)) return FALSE;
  foreach ($dat->assets as $j) {
    if (isset($j->browser_download_url)) return $j->browser_download_url;
  }
  return FALSE;
}

echo "Reading manifest: $manifest\n";
$cfgtxt = file($manifest);
if ($cfgtxt === FALSE) die("Error reading manifest: $manifest\n");
$cfg = [];
$cnt = 0;
foreach ($cfgtxt as $ln) {
  ++$cnt;
  $ln = trim($ln);
  if ($ln == '' || $ln{0} == '#') continue;
  
  $ln = preg_split('/\s+/',$ln);
  if (count($ln) < 2) {
    echo "Incomplete line at $cnt\n";
    continue;
  }
  $class = strtolower(array_shift($ln));
  $extname = array_shift($ln);
  if (count($ln)) {
    $url = array_shift($ln);
    if ($url{0} == '#') $url = FALSE;
  } else {
    $url = FALSE;
  }
  if (!isset($classes[$class])) {
    echo "Invalid class: $class at $cnt\n";
    continue;
  }
  $cfg[implode(':',[$class,$extname])] = [
    "class" => $class,
    "name" => $extname,
    "url" => $url,
  ];
}

if (count($cfg) == 0) {
  echo "No extensions needed\n";
  exit(0);
}

$ext = '.zip';
$items = [];

// Make a list of existing extensions
foreach ($classes as $class) {
  $extdir = $data_dir.'ext-'.$class.'s/';
  foreach (glob($extdir.'*'.$ext) as $zip) {
    $name = basename($zip,$ext);
    $items[implode(':',[$class,$name])] = [ $class,$name ];
  }
}
echo count($items)." extensions already present\n";

foreach ($cfg as $id=>$c) {
  if (isset($items[$id])) unset($items[$id]);
  
  $extdir = $data_dir.'ext-'.$c['class'].'s/';
  if (!is_dir($extdir)) mkdir($extdir);

  $zip = $extdir.$c['name'].$ext;
  if (is_file($zip)) continue; // Already there... we skip this step

  if ($c['url'] == FALSE) {
    echo "Resolving ".$c['name']."...";
    $url = get_url($c['class'],$c['name']);
    if ($url == FALSE) {
      echo "ERROR\n";
      continue;
    }
  } elseif (preg_match('/^github:/',$c['url'])) {
    echo "Looking up ".$c['url']."...";
    $url = get_github_url($class,$name,preg_replace('/^github:/','',$c['url']));
    if ($url == FALSE) {
      echo "ERROR\n";
      continue;
    }
  } else {
    $url = strtr($c['url'],[
      '{name}'=>$c['name'],
      '{class}'=>$c['class'],
    ]);
  }
  echo "Feching $url...";
  exec('curl -Ls '.$url.' -o '.$zip, $output, $rvar);
  if ($rvar) {
    echo "Failed\n";
    cunlink($zip);
    continue;
  }
  echo "Verifying...";
  exec('unzip -t '.$zip, $output, $rvar);
  if ($rvar) {
    cunlink($zip);
    echo "Failed\n";
    continue;
  }
  echo "OK\n";
  
}
// Clean-up things...
foreach ($items as $i=>$j) {
  list($class,$name) = $j;
  echo 'Removing '.$i.PHP_EOL;
  cunlink($data_dir.'ext-'.$class.'s/'.$name.$ext);
}
