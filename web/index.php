<?php

use DragonBe\Vies\Vies;
use DragonBe\Vies\ViesServiceException;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';

Sentry\init(['dsn' => $config['sentry']]);


$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}
if (!file_exists($filename)) {
    http_response_code(404);
    Sentry\captureMessage('404 triggered on ' . $filename);
    return include __DIR__ . '/404.php';
}

$countryCodes = [
    'AT' => 'Austria',
    'BE' => 'Belgium',
    'BG' => 'Bulgaria',
    'CY' => 'Cyprus',
    'CZ' => 'Czech Republic',
    'DE' => 'Germany',
    'DK' => 'Danmark',
    'EE' => 'Estonia',
    'EL' => 'Greece',
    'ES' => 'Spain',
    'FI' => 'Finland',
    'FR' => 'France',
    'HR' => 'Hungary',
    'IE' => 'Ireland',
    'IT' => 'Italy',
    'LU' => 'Luxembourg',
    'LV' => 'Latvia',
    'LT' => 'Lithuania',
    'MT' => 'Malta',
    'NL' => 'Netherlands',
    'PL' => 'Poland',
    'PT' => 'Portugal',
    'RO' => 'Romania',
    'SE' => 'Sweden',
    'SI' => 'Slovenia',
    'SK' => 'Slovakia',
    'GB' => 'United Kingdom - Unsupported since 2021-01-01',
    'XI' => 'United Kingdom (Northern Ireland)',
    'EU' => 'MOSS Number',
];

$result = null;
$error = false;
if ([] !== $_POST) {
    $target = array (
        'country' => (isset ($_POST['target-country']) && '' !== $_POST['target-country']) ? $_POST['target-country'] : null,
        'vat' => (isset ($_POST['target-vat']) && '' !== $_POST['target-vat']) ? $_POST['target-vat'] : null,
    );
    if ('' === (string) $target['country'] || !in_array($target['country'], array_keys($countryCodes))) {
        $result = 'Country code is incorrect';
        $error = true;
    }
    if ('' === (string) $target['vat']) {
        $result = 'VAT requires a valid value';
        $error = true;
    }
    if (false === $error) {
        $vies = new Vies();
        try {
            $result = $vies->validateVat($target['country'], $target['vat']);
        } catch (ViesServiceException $viesServiceException) {
            $result = $viesServiceException->getMessage();
            Sentry\captureException($viesServiceException);
        } catch (\DragonBe\Vies\ViesException $viesException) {
            $result = 'Problem validating VAT ID: ' . $viesException->getMessage();
            Sentry\captureException($viesException);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="content-type" content="text/html"/>
        <title>Validate European VAT</title>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="stylesheet" href="assets/css/bootstrap.css"/>
        <link rel="stylesheet" href="assets/css/bootstrap-theme.css"/>
        <link rel="stylesheet" href="assets/css/style.css"/>
    </head>
    <body>
        <div id="fork-us">
            <a class="fork-us-link" href="https://github.com/dragonbe/vies" title="Fork us on GitHub">
                <img src="assets/images/fork-us-on-github.png" width="149" height="149" alt="Fork us on GitHub"/>
            </a>
        </div>
        <div class="jumbotron">
            <div class="container">
                <div class="logo-box">
                    <div style="float: left; width: 200px; padding-top: 20px; display: block;">
                        <img class="img-circle" src="https://cdn.in2it.be/dragonbe/vies_square.png" alt="DragonBe VIES Logo" width="200" height="200">
                    </div>
                    <div style="float: left; width: 680px; margin-left: 20px; display: block;">
                        <h1>Validate European VAT</h1>
                        <p>Quick and easy interface to validate <a title="VIES Service provided by EC">VAT Information Exchange System (VIES)</a> of the European Commission (EC). This application is a frontend for the PHP package <a href="https://github.com/DragonBe/vies" title="dragonbe/vies on GitHub">dragonbe/vies</a> which you can use in your PHP applications.</p><p>This service is provided for free on <a href="https://vies-web.azurewebsites.net">vies-web.azurewebsites.net</a>.</p>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <div class="container">
            <?php if (null !== $result): ?>
                <?php if($result instanceof \DragonBe\Vies\CheckVatResponse && $result->isValid()): ?>
                    <div class="row">
                        <div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-ok-sign"></span> VAT registration number is valid</div>
                    </div>
                <?php elseif ($result instanceof \DragonBe\Vies\CheckVatResponse && false === $result->isValid()): ?>
                    <div class="row">
                        <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign"></span> VAT registration number is NOT valid</div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="alert alert-danger"><p><span class="glyphicon glyphicon-info-sign"></span> <?php echo $result ?></p></div>
                    </div>
                <?php endif ?>
            <?php endif ?>
            <div class="row">
                <form id="vat-validator" role="form" class="form-horizontal" enctype="application/x-www-form-urlencoded" method="post" action="<?php echo substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], 'index.php')) ?>">
                    <fieldset>
                        <legend>Business information</legend>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="target-country">Country code</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="target-country" id="target-country">
                                    <?php foreach ($countryCodes as $code => $country): ?>
                                        <?php if (isset ($target['country']) && $code === $target['country']): ?>
                                            <option value="<?php echo $code ?>" selected="selected"><?php echo $country ?></option>
                                        <?php else: ?>
                                            <option value="<?php echo $code ?>"><?php echo $country ?></option>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="target-vat">VAT registration number</label>
                            <div class="col-sm-8">
                                <?php if (isset ($target['vat'])): ?>
                                    <input class="form-control" type="text" name="target-vat" id="target-vat" value="<?php echo $target['vat'] ?>"/>
                                <?php else: ?>
                                    <input class="form-control" type="text" name="target-vat" id="target-vat" placeholder="123456789"/>
                                <?php endif ?>
                            </div>
                        </div>
                    </fieldset>
                    <button id="verify" class="btn btn-success"><span class="glyphicon glyphicon-check"></span> Validate</button>
                    <a id="clear" class="btn btn-default" href="<?php echo substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], 'index.php')) ?>">Clear</a>
                </form>
            </div>
        </div>
        <script type="application/javascript" src="assets/js/jquery-1.11.1.js"/>
        <script type="application/javascript" src="assets/js/bootstrap.js"/>
        <script type="application/javascript">
            jQuery(document).ready(function () {
                jQuery.("#verify").click(function () {
                    jQuery.("#vat-validator").submit();
                });
            });
        </script>
    </body>
</html>
