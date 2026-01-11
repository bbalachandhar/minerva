<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <title><?php echo $this->setting[0]['name']; ?></title>
    </head>
    <body>
        <?php if (isset($api_error)) {
            echo "<div class='alert alert-danger'>" . $api_error . "</div>";
        }
        ?>

        <p>Please wait, your order is being processed and you will be redirected to the billdesk website.</p>

        <form name="sdklaunch" id="sdklaunch" action="<?php echo $form_action; ?>" method="POST">
            <?php
            if (isset($fields)) {
                foreach ($fields as $key => $value) {

                    if (is_array($value)) {
                        foreach ($value as $nested_key => $nested_value) {
                            echo '<input type="hidden" name="' . $key . '[' . $nested_key . ']" value="' . $nested_value . '">';
                        }
                    } else {
                        echo '<input type="hidden" name="' . $key . '" value="' . $value . '">';
                    }
                }
            }
            ?>

            <input type='submit' value='Complete your Payment' />
        </form>
        <script>
            document.getElementById("sdklaunch").submit();
        </script>
    </body>
</html>
