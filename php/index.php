<!doctype html>
<?php
require_once 'Counter.class.php'; 
$counter = new Counter();
?>
<html>

<head>
  <title>PHP Visitor Counter</title>
  <meta charset="UTF-8" />
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
  <h1>PHP Visitor Counter</h1>
  
  <h2>Live Example</h2>
  
  <table>
      <tr>
        <td>plain text</td>
        <td><?= $counter->asText() ?></td>
      </tr>
      <tr>
        <td>series of single images</td>
        <td><?= $counter->asHTMLImages() ?></td>
      </tr>
      <tr>
        <td>assembled as one image</td>
        <td><img src="ImageCounter.png.php" alt="<?= $counter->asText() ?>" /></td>
      </tr>
  </table>

  <h2>Predefined Counter Styles</h2>
  
  <table>
    <thead>
      <tr>
        <th>style name</th>
        <th>example</th>
    </thead>
    <tbody>
<?php 
  $stylesDirectory = dirname(__FILE__) . '/styles';
  if (is_dir($stylesDirectory)) {
    foreach (scandir($stylesDirectory, SCANDIR_SORT_ASCENDING) as $style) {
      if (strpos($style, '.') === 0) {
        continue;
      }
?>
     <tr>
       <td><?= $style?></td>
       <td class="example">
<?php 
      for ($i = 0; $i < 10; $i++) {
        $url = 'styles/' . $style . '/' . $i . '.png';
?>
          <img src="<?= $url ?>" alt="<?= $i ?>" />
<?php
      }
?>
      </td>
     </tr>
<?php
    }
  }
?>
    </tbody>
  </table>
  
  <h2>Technologies / Conformance</h2>
  
  <a href="http://www.php.net">
    <img src="powered_by_php.png" width="88" height="31" alt="Powered by PHP!" />
  </a>
  
  <a href="http://www.w3.org/html/logo/">
    <img src="http://www.w3.org/html/logo/badge/html5-badge-h-solo.png" width="32" height="32" alt="HTML5 Powered" title="HTML5 Powered">
  </a>
  
  <a href="http://jigsaw.w3.org/css-validator/check/referer">
    <img src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" height="31" width="88"  />
  </a>
  
</body>
</html>
