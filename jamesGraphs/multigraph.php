
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html> 
 <head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
    <title>Flot Examples</title> 
    
    <!--[if lte IE 8]><script language="javascript" type="text/javascript" src="../excanvas.min.js"></script><![endif]--> 
    <script language="javascript" type="text/javascript" src="../pem/js/jquery-1.5.2.min.js"></script> 
    <script language="javascript" type="text/javascript" src="../flot/jquery.flot.js"></script> 
 </head> 
    <body> 
    <h1>Flot Examples</h1> 
 
    <div id="placeholder" style="width:600px;height:300px;"></div> 
 
    <p>Here is an example with real data: military budgets for
        various countries in constant (2005) million US dollars (source: <a href="http://www.sipri.org/">SIPRI</a>).</p> 
 
    <p>Since all data is available client-side, it's pretty easy to
       make the plot interactive. Try turning countries on/off with the
       checkboxes below.</p> 
 
    <p id="choices">Show:</p> 
 
<script type="text/javascript"> 



$(function () {
    


	$.getJSON('multiSensor.php?hubId=14', function(data)
	{
		var datasets = data.dataset;
	});
	
    // hard-code color indices to prevent them from shifting as
    // countries are turned on/off
    var i = 0;
    $.each(datasets, function(key, val) {
        val.color = i;
        ++i;
    });
    
    // insert checkboxes 
    var choiceContainer = $("#choices");
    $.each(datasets, function(key, val) {
        choiceContainer.append('<br/><input type="checkbox" name="' + key +
                               '" checked="checked" id="id' + key + '">' +
                               '<label for="id' + key + '">'
                                + val.label + '</label>');
    });
    choiceContainer.find("input").click(plotAccordingToChoices);
 
    
    function plotAccordingToChoices() {
        var data = [];
 
        choiceContainer.find("input:checked").each(function () {
            var key = $(this).attr("name");
            if (key && datasets[key])
                data.push(datasets[key]);
        });
 
        if (data.length > 0)
            $.plot($("#placeholder"), data, {
                yaxis: { min: 0 },
                xaxis: { tickDecimals: 0 }
            });
    }
 
    plotAccordingToChoices();
});
</script> 
 
 </body> 
</html>