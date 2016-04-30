<?php
/**
 * Network Status Monitor
 * This network status monitor is set up to interface
 * with PRTG and use information provided to display a
 * simple responsive view of the status of network devices.
 */
include('prtg_interface.php');

// Build the Network Status object.
$network_status = new NetworkStatus();

// Filter out specific Object Properties for quicker access.
$vs = $network_status->virtualization_services;
$ic = $network_status->internet_connectivity;
$nd = $network_status->network_distribution;
$wc = $network_status->wireless_connectivity;
$ri = $network_status->residential_infrastructure;
$ts = $network_status->telephony_services;
$ws = $network_status->web_services;
$ss = $network_status->surveillance_services;
$ns = $network_status->network_services;

print_r($ns);
        
/**
 *  Define Status Overview Class
 *  This class determines what color the status overview
 *  is anytime its displayed.
 */
function status_overview($status) {
    if ($status == 100 || $status == 'Up') {
        return 'success-status';
    } elseif ($status > 90 && $status < 100 || $status == 'Warning') {
        return 'warning-status';
    } else {
        return 'alert-status';
    }
}

function tip_lookup($string) {
    $string = str_replace('STA', '<a class="tip-link" href="#tip-sta">STA</a>', $string);
    $string = str_replace('AP', '<a class="tip-link" href="#tip-ap">AP</a>', $string);
    $string = str_replace('WAN', '<a class="tip-link" href="#tip-wan">WAN</a>', $string);
    return $string;
}

function sanitize_traffic_kbit($raw_traffic) {
    return intval(str_replace(',', '', str_replace( ' kbit/s', '', $raw_traffic)));
}

?>

<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<meta http-equiv="refresh" content="20" />-->
    <title>YWAM Montana | Lakeside Network Status</title>
    <link rel="stylesheet" href="{{root}}assets/css/app.css">
    <link rel="stylesheet" type="text/css" href="{{root}}assets/font/flaticon.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.0.0/Chart.js"></script>
  </head>
  <body>

<div class="row">
    <div class="large-4 medium-6 columns">
        <?php include('ic_snapshot.php'); // Internet Connectivity Snapshot ?>
        <?php include('ws_snapshot.php'); // Web Services Snapshot ?>
    </div>
    
    <div class="large-4 medium-6 columns">
        <?php include('iu_snapshot.php'); // Internet Usage Snapshot ?>
        <?php include('nd_snapshot.php'); // Network Distribution Snapshot ?>
        <?php include('ts_snapshot.php'); // Telephony Services Snapshot ?>
    </div>
    
    <div class="large-4 medium-6 columns">
        <?php include('wc_snapshot.php'); // Wireless Connectivity Snapshot ?>
        <?php include('ss_snapshot.php'); // Surveillance Services Snapshot ?>
        <?php include('vs_snapshot.php'); // Virtualization Services Snapshot ?>
    </div>
</div>

<div class="row">
    <div class="medium-12 columns">
        <div class="device-drilldown">
            <h2 class="device-drilldown-title">Devices & Services Drilldown</h2>
        </div>
    </div>
</div>


<div class="row">
    <div id="tablesContainer" class="large-8 columns">    

    <?php
    include('ic_table.php'); // Internet Connectivity Table
    include('ws_table.php'); // Web Services Table
    include('nd_table.php'); // Network Distribution
    include('wc_table.php'); // Wireless Connectivity
    include('vs_table.php'); // Virtual Services Table
    include('ns_table.php'); // Network Services Table
    include('ts_table.php'); // Telephony Services Table
    include('ri_table.php'); // Residential Infrastructure Table
    include('ss_table.php'); // Surveillance Services Table
    ?>
        
        
        <div class="tip-container">
            <section id="tip-sta" data-magellan-target="tip-sta">
                <h5 class="tip"><i class="flaticon-technology-1"></i> STA - Station</h5>
                <p>A station (STA) is an endpoint in a Point to Point (PtP), or a Point to Multi-Point (PtMP) directional link. In most cases this provides some sort of network connectivity to the structure it is installed on.</p>
            </section>
            
            <section id="tip-ap" data-magellan-target="tip-ap">
                <h5 class="tip"><i class="flaticon-technology-2"></i> AP - Access Point</h5>
                <p>An access point (AP) is a device that, typically, transmits a signal that can be used by any WiFi compatible client to provide network connectivity. All wireless clients whether it be a phone, computer or a tablet access the network through a wireless access point.</p>
            </section>
            
            <section id="tip-wan" data-magellan-target="tip-wan">
                <h5 class="tip"><i class="flaticon-technology-3"></i> WAN - Wide Area Network</h5>
                <p>A wide area network (WAN) is a telecommunications network or computer network that extends over a large geographical distance. These types of connections are most commonly known as internet connections that provide routing to publicly available services.</p>
            </section>
        </div>
    
        
    </div>
    
    
    <div class="large-4 columns sidebar" data-sticky-container>
        <div class="sticky sticky-sidebar" data-sticky data-anchor="tablesContainer">
            <table class="medium-12 columns" data-magellan>
                <tr>
                    <td class="icon-container"><i class='flaticon-technology-4'></i></td>
                    <td><a href="#ic">Internet Connectivity</a></td>
                    <td class="success-status status-container">999/999</td>
                </tr>
                <tr>
                    <td class="icon-container"><i class='flaticon-earth-globe'></i></td>
                    <td><a href="#ws">Web Services</a></td>
                    <td class="status-container <?php echo status_overview($ws['percent_web_services_up']) ?>">
                        <?php echo $ws['num_web_services_up'] . '/' . $ws['num_web_services']; ?>
                    </td>
                </tr>
                <tr>
                    <td class="icon-container"><i class='flaticon-connection-1'></i></td>
                    <td><a href="#nd">Network Distribution</a></td>
                    <td class="status-container <?php echo status_overview($percent_nd_devices_up) ?>">
                        <?php echo $num_nd_devices_up . '/' . $num_nd_devices; ?>
                    </td>
                </tr>
                <tr>
                    <td class="icon-container"><i class='flaticon-technology-3'></i></td>
                    <td><a href="#nd">Wireless Connectivity</a></td>
                    <td class="status-container <?php echo status_overview($wc['percent_campus_aps_up']) ?>">
                        <?php echo $wc['num_campus_aps_up'] . '/' . $wc['num_campus_aps']; ?>
                    </td>
                </tr>
                <tr>
                    <td class="icon-container"><i class='flaticon-technology-5'></i></td>
                    <td><a href="#vs">Virtualization Services</a></td>
                    <td class="status-container <?php echo status_overview($vs['percent_vs_devices_up']) ?>">
                        <?php echo $vs['num_vs_devices_up'] . '/' . $vs['num_vs_devices']; ?>
                    </td>
                </tr>
                <tr>
                    <td class="icon-container"><i class='flaticon-technology-3'></i></td>
                    <td><a href="#ns">Network Services</a></td>
                    <td class="status-container <?php echo status_overview($wc['percent_campus_aps_up']) ?>">
                        <?php echo $wc['num_campus_aps_up'] . '/' . $wc['num_campus_aps']; ?>
                    </td>
                </tr>
                <tr>
                    <td class="icon-container"><i class='flaticon-technology-2'></i></td>
                    <td><a href="#ts">Telephony Services</a></td>
                    <td class="status-container <?php echo status_overview($ts['percent_ts_devices_up']) ?>">
                        <?php echo $ts['num_ts_devices_up'] . '/' . $ts['num_ts_devices']; ?>
                    </td>
                </tr>
                <tr>
                    <td class="icon-container"><i class='flaticon-real-estate'></i></td>
                    <td><a href="#ri">Residential Infrastructure</a></td>
                    <td class="success-status status-container">999/999</td>
                </tr>
                <tr>
                    <td class="icon-container"><i class='flaticon-video'></i></td>
                    <td><a href="#ss">Surveillance Services</a></td>
                    <td class="status-container <?php echo status_overview($ss['percent_ss_devices_up']) ?>">
                        <?php echo $ss['num_ss_devices_up'] . '/' . $ss['num_ss_devices']; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
      <script src="{{root}}assets/js/app.js"></script>
  </body>
</html>