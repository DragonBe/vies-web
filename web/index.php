<?php
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    $countryCodes = array (
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
        'UK' => 'United Kingdom',
    );

    $result = null;
    if (array () !== $_POST) {
        $target = array (
            'country' => (isset ($_POST['target-country']) && '' !== $_POST['target-country']) ? $_POST['target-country'] : null,
            'vat' => (isset ($_POST['target-vat']) && '' !== $_POST['target-vat']) ? $_POST['target-vat'] : null,
        );
        if (null === $target['country'] || !in_array($target['country'], array_keys($countryCodes))) {
            $result = 'Country code is incorrect';
        }
        if (null === $target['vat']) {
            $result = 'VAT requires a valid value';
        }
        try {
            $vies = new \DragonBe\Vies\Vies();
            $result = $vies->validateVat($target['country'], $target['vat']);
        } catch (\SoapFault $e) {
            $result = 'VAT registration service VIES is unavailable right now';
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
    </head>
    <body>
        <div class="container">
            <div class="row">
                <h1>Validate European VAT</h1>
            </div>
            <?php if (null !== $result): ?>
                <?php if($result instanceof \DragonBe\Vies\CheckVatResponse && $result->isValid()): ?>
                    <div class="row">
                        <div class="col-md-12 bg-success"><div class="center-block"><p><span class="glyphicon glyphicon-ok-sign"> VAT registration number is valid</span></p></div></div>
                    </div>
                <?php elseif ($result instanceof \DragonBe\Vies\CheckVatResponse && false === $result->isValid()): ?>
                    <div class="row">
                        <div class="col-md-12 bg-danger"><div class="center-block"><p><span class="glyphicon glyphicon-exclamation-sign"> VAT registration number is NOT valid</span></p></div></div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-12 bg-info"><div class="center-block"><p><span class="glyphicon glyphicon-info-sign"> <?php echo $result ?></span></p></div></div>
                    </div>
                <?php endif ?>
            <?php endif ?>
            <div class="row">
                <form id="vat-validator" role="form" class="form-horizontal" enctype="application/x-www-form-urlencoded" method="post" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>">
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
                            <div class="col-sm-2"><span class="glyphicon glyphicon-question-sign"/></div>
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
                            <div class="col-sm-2"><span class="glyphicon glyphicon-question-sign"/></div>
                        </div>
                    </fieldset>
                    <button id="verify" class="btn btn-success"><span class="glyphicon glyphicon-check"> Validate</span></button>
                </form>
            </div>
        </div>
        <script type="application/javascript" src="assets/js/jquery-1.11.1.js"/>
        <script type="application/javascript" src="assets/js/bootstrap.js"/>
        <script type="application/javascript">
            jQuery.("#verify").click(function () {
                jQuery.("#vat-validator").submit();
            });
        </script>
    </body>
</html>