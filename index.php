<?php

$data = json_decode(file_get_contents('http://api.fixer.io/latest?base=USD'),true);



$today = date('Y-m-d');
//$file = fopen("http://api.coindesk.com/v1/bpi/historical/close.csv?start=2010-01-01&end=".$today."&index=USD","r");

$file= fopen('php://temp', 'w+');

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "http://api.coindesk.com/v1/bpi/historical/close.csv?start=".date('Y-m-d',strtotime('-30 days'))."&end=".$today."&index=USD");
curl_setopt($curl, CURLOPT_FILE, $file);

curl_exec($curl);
curl_close($curl);

rewind($file);


$exchange_rates = array();
$dates = array();
$current_btc_price = 0.0;
$btc_to_peso = 0.0;

while (($line = fgetcsv($file)) !== FALSE) {
  
if($line!=NULL && !empty($line[1]) && $line[1]!='Close'):
	array_push($dates,$line[0]);
	array_push( $exchange_rates , $line[1]);
endif;

}

fclose($file);

$current_btc_price =  end($exchange_rates);

//Bitcoin to Philippine Peso conversion
$btc_to_peso = ($data['rates']['PHP'] *  $current_btc_price );

?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<meta name="description" content="Bitcoin Historical Chart" />
	 <meta name="keywords" content="BTC chart" />
	<title>BTC EXCHANGE RATE HISTORICAL DATA</title>
</head>
<body>
	<style type="text/css">
		
		body {
    	font-family: "Arial", Helvetica, sans-serif;
		}

	</style>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>

    <script>
        var config = {
            type: 'line',
            data: {
                labels: [<?php echo "'" . implode("','", $dates) . "'"; ?>],
                datasets: [{
                    label: "BITCOIN EXCHANGE RATE HISTORICAL DATA (IN US Dollar)",
                    backgroundColor: 'rgb(54, 162, 235)',
                    borderColor: 'rgb(54, 162, 235)',
                    data: [<?php echo implode(',', $exchange_rates); ?>],
                    fill: false,
                }]
            }
        };

        window.onload = function() {
            var ctx = document.getElementById("canvas").getContext("2d");
            window.myLine = new Chart(ctx, config);
        };
    </script>
  <div style="text-align: center; font-weight: bold; font-size: 20px;">
  	1 Bitcoin = PHP <?php echo number_format($btc_to_peso,2); ?> 
  </div>
  <div style="width:100%;">
        <canvas id="canvas"></canvas>
  </div>

  <div style="text-align: center;">
  	<strong>&copy; Created by <a href="/">Novi</a></strong>
  </div>
</body>
</html>