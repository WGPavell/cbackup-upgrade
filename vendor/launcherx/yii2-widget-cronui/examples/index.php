<!DOCTYPE html>
<html lang="en">
<head>
    <title>CronUI Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/cronui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/css/bootstrap-select.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="../assets/js/jquery.cronui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
</head>
<body>

<div class="container">
    <div class="row">
        <!-- Content
        =========================================== -->

            <h3 id="ex-basic-demo">Basic demo</h3>
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="cron-ui"></div>
                        </div>
                    </div>
                    <div class="row" style="margin: 15px">
                        <div class="form-group">
                            <input type="text" id="result_out" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function () {

        /** Init cronui plugin */
        var c = $('.cron-ui').cronui({
            dropDownMultiple: true,
            dropDownStyled: true
        });

        /** Set value */
        c.cronui('setValue', '39,42 23 * * 1,0');

    });
</script>

</body>
</html>
