<?php
/**
 * Network Status Monitor
 * This network status monitor is set up to interface
 * with PRTG and use information provided to display a
 * simple responsive view of the status of network devices.
 */
$protocol = $_SERVER[ 'NETWORKSTATUS_PROTOCOL' ];
$server = $_SERVER['NETWORKSTATUS_SERVER' ];
$port = $_SERVER[ 'NETWORKSTATUS_SERVERPORT' ];
$username = $_SERVER[ 'NETWORKSTATUS_USERNAME' ];
$passhash = $_SERVER[ 'NETWORKSTATUS_PASSHASH' ];

// Define Socket and File Information
$socket = "{$protocol}://{$server}:{$port}";
$credentials = "username={$username}&passhash={$passhash}";
$filename = "{$socket}/api/table.xml?content=sensortree&{$credentials}";

$arrContextOptions = array(
    "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
    ),
);

if (($response_xml_data = file_get_contents("{$filename}", false, stream_context_create($arrContextOptions)))===false) {
    echo "Error fetching XML\n";
} else {
   libxml_use_internal_errors(true);
   $data = simplexml_load_string($response_xml_data);
   if (!$data) {
       echo "Error loading XML\n";
       foreach(libxml_get_errors() as $error) {
           echo "\t", $error->message;
       }
   }
}

// Trim the XML Document down to just the groups needed.
$groups = $data->sensortree->nodes->group->probenode;

// Assign Specific Groups to Variables for easier access.
foreach ($groups->group as $group) {
    $group->name == "Network Infrastructure" ? $network_infrastructure = $group : null;
}

// Assign Specific Sub Groups to Variables for further easier access.
foreach($network_infrastructure->group as $group) { 
    $group->name == "Residential Infrastructure" ? $residential_infrastructure = $group : null;
    $group->name == "Wireless Access Points" ? $wireless_access_points = $group : null;
    $group->name == "Switches" ? $switches = $group : null;
    $group->name == "Wireless Backhaul Devices" ? $wb_devices = $group : null;
}


/**
 *  Gather Internet Statistics
 *  Here we will gather all of the WAN interfaces, and
 *  poll the traffic together to come up with an aggregate
 *  internet traffic value, along with generating basic uptime
 *  values.
 */
$num_wan_connections = $num_wan_connections_down = $total_wan_traffic = 0;
foreach ($network_infrastructure->device as $device) {
    if ($device->name == "YWAMMT-COREROUTER") {
        $core_router = $device;
        foreach ($device->sensor as $sensor) {
            if (strpos($sensor->name, 'WAN') !== false) {
                // Fill up a WAN Connections Array
                $wan_connections[] = $sensor;
                $num_wan_connections++;
                $sensor->status == 'Down' ? $num_wan_connections_down++ : null;
                $total_wan_traffic = $total_wan_traffic + $sensor->lastvalue;
            }
            
            if($sensor->name == "PING") {
                $core_router_status = $sensor->status;
            }
        }
    }
}
// Define the number of WAN Connections Up
$num_wan_connections_up = $num_wan_connections - $num_wan_connections_down;
// Define Percentage of WAN Connections Up
$percent_wan_connections_up = ($num_wan_connections_up/$num_wan_connections)*100;


/**
 *  Gather Statistics for Switches
 *  Here we will gather statistics and counts for the distribution
 *  switches on the network.
 */
$num_switches = $num_switches_down = $num_dist_switches = $num_dist_switches_down = 0;
foreach ($switches as $switch) {
    $num_switches++;
    foreach ($switch->sensor as $sensor) {
        if ($sensor->name == 'PING') {
            if($switch->name == "YWAMMT | SWCH | Core Switch") {
                $core_switch_status = $sensor->status;
                if ($sensor->status == "Down") {
                    $num_switches_down++;
                }
                $core_switch_status = $sensor->status;
            } else {
                if ($sensor->status == "Down") {
                    $num_switches_down++;
                    $num_dist_switches_down++;
                }
            }
        }
    }
}
// Remove Core Switch from Distribution Switches.
$num_dist_switches = $num_switches - 1;

// Calculate Switches Up
$num_switches_up = $num_switches - $num_switches_down;
$num_dist_switches_up = $num_dist_switches - $num_dist_switches_down;

// Calculate Switches Up Percentage
$switches_up_percent = ($num_switches / $num_switches_up)*100;
$dist_switches_up_percent = ($num_dist_switches / $num_dist_switches_up)*100;




/**
 *  Count and Gather Access Point Statistics
 *  Here we will count and gather different statistics about our
 *  wireless access points around the campus.
 */
$num_wireless_aps = $num_wireless_aps_down = 0;
foreach ($wireless_access_points->device as $wireless_ap) {
    $num_wireless_aps++;
    foreach ($wireless_ap->sensor as $sensor) {
        if ($sensor->name == 'PING' && $sensor->status == "Down") {
            $num_wireless_aps_down++;
        }
    }
}


/**
 *  Wireless Backhaul Devices
 *  Here we will count all of the wireless backhaul devices
 *  used to wireless distribute the network over the beautful
 *  landscape that is Montana.
 */
$num_wb_devices = $num_wb_devices_down = 0;
foreach ($wb_devices->device as $device) {
    $num_wb_devices++;
}


/**
 *  Count Residential Infrastructure Devices & Gather Statuses
 *  We use this section to gather statistics about how many devices
 *  currently reside in this group, along with interpreting data based
 *  the current status of specific sensors.
 */

// Declare Variables
$num_ri_devices = $num_ri_devices_down = 0;

// Count Devices in Residential Infrastructure Group
foreach ($residential_infrastructure->device as $device) {
    $num_wb_devices++;
    foreach($device->sensor as $sensor) {
        if ($sensor->name == 'PING' && $sensor->status == 'Down') {
            $num_wb_devices_down++;
        }
    }
}

// Count Devices in Each Individual Residence Group.
foreach ($residential_infrastructure->group as $residence) {
    foreach ($residence->device as $device) {
        $num_ri_devices++;
        foreach ($device->sensor as $sensor) {
            if ($sensor->name == "PING") {
                
                if ($sensor->status == "Down") {
                    $num_ri_devices_down++;
                }
                
                if (strpos($device->name, 'STA') !== false) {
                    $num_wb_devices++;
                    if ($sensor->status == "Down"){
                        $num_wb_devices_down++;
                    }
                }
            }
        }
    }
}
// Declare Number of Residential Infrastructure Devics Up
$num_ri_devices_up = $num_ri_devices - $num_ri_devices_down;

/**
 *  Finalize Count of Wireless Backhaul Devices
 *  Here we will sum up all of the wireless backhaul
 *  device statistics we have.
 */
$num_wb_devices_up = $num_wb_devices - $num_wb_devices_down;
$percent_wb_devices_up = ($num_wb_devices_up/$num_wb_devices)*100;



/**
 *  Define Status Overview Class
 *  This class determines what color the status overview
 *  is anytime its displayed.
 */
function status_overview($status) {
    if ($status == 100 || $status == 'Up') {
        return 'success-status';
    } elseif ($status > 90 && $status < 100) {
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

?>

<div class="row">
    <div class="large-4 medium-6 columns">
        
        <?php include('ic_snapshot.php'); ?>
        
    </div>
    
    <div class="large-4 medium-6 columns">
        
        <?php // Count total number of network distribution devices.
        
        $num_nd_devices = $num_nd_devices_down = $num_nd_devices_up = 0;
        // Give static number for Core Devices
        $core_nd_devices = 2 + $num_dist_switches + $num_wb_devices;
        $core_nd_devices_down = 0 + $num_dist_switches_down + $num_wb_devices_down;
        
        $core_router_status == "Down" ? $core_nd_devices_down++ : null;
        $core_switch_status == "Down" ? $core_nd_devices_down++ : null;
        
        $core_nd_devices_up = $core_nd_devices - $core_nd_devices_down;
        $percent_core_nd_devices_up = ($core_nd_devices_up/$core_nd_devices_up)*100;
        ?>
        
        <table class="snapshot-container">
            <thead>
                <tr class="snapshot-title">
                    <th class="snapshot-logo"><i class='flaticon-connection-1'></i></th>
                    <th><h6>Network Distribution</h6></th>
                    <th class="snapshot-overview <?php echo status_overview($percent_core_nd_devices_up); ?>"><div><?php echo $core_nd_devices_up . '/' . $core_nd_devices; ?></div></th>
                </tr>
            </thead>
            <tbody>
                
                <tr>
                    <td colspan="2">Core Router</td>
                    <td class="<?php echo status_overview($core_router_status); ?>"><?php echo $core_router_status; ?></td>
                </tr>
                <tr>
                    <td colspan="2">Core Switch</td>
                    <td class="<?php echo status_overview($core_switch_status); ?>"><?php echo $core_switch_status; ?></td>
                </tr>
                <tr>
                    <td colspan="2">Distribution Switches</td>
                    <td class="snapshot-status <?php echo status_overview($dist_switches_up_percent); ?>"><?php echo $num_dist_switches . '/' . $num_dist_switches_up; ?></td>
                </tr>
                <tr>
                    <td colspan="2">Wireless Backhaul Devices</td>
                    <td class="snapshot-status <?php echo status_overview($percent_wb_devices_up); ?>"><?php echo $num_wb_devices . '/' . $num_wb_devices_up; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="large-4 medium-6 columns">
        
    </div>
</div>

<div class="row">
    
    
    <div id="tablesContainer" class="large-8 columns">    
        
        
        
        
    <?php
        
        
        
        
        
        
        
    // Internet Connectivity
    include('ic_table.php');
        
    // Residential Infrastructure Table
    include('ri_table.php'); ?>
        
        
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
            <table class="medium-12 columns">
                <tr>
                    <td class="icon-container"><i class='flaticon-technology-3'></i></td>
                    <td>Internet Connectivity</td>
                    <td class="success-status status-container">999/999</td>
                </tr>
                <tr>
                    <td class="icon-container"><i class='flaticon-technology-1'></i></td>
                    <td>Residential Infrastructure</td>
                    <td class="success-status status-container">999/999</td>
                </tr>
            </table>
        </div>
    </div>
    
    

</div>