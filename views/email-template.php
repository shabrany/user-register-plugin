<!doctype html>
<html>
    <body>
        <p>Nieuw registratie  voltooid. Bekijk hieronder het resultaat</p>
        <?php foreach($data as $label => $value): ?>
        <p>
            <strong style="display: block;"><?php echo $label ?></strong>
            <span><?php echo $value; ?></span>
        </p>
        <?php endforeach; ?>
    </body>
</html>
