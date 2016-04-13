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
}

// Count Devices in Residential Infrastructure Group
foreach ($residential_infrastructure->group as $residence) {
    foreach ($residence->device as $device) {
        isset($num_ri_devices) ? $num_ri_devices++ : $num_ri_devices = 1;
        isset($num_ri_devices_down) ? $num_ri_devices_down++ : $num_ri_devices_down = 1; 
    }
}

/**
 *  Define Status Overview Class
 *  This class determines what color the status overview
 *  is anytime its displayed.
 */
function status_overview($percentage) {
    if ($percentage = 100) {
        return 'status-overview-success';
    } elseif ($percentage > 90 && $percentage < 100) {
        return 'status-overview-warning';
    } else {
        return 'status-overview-alert';
    }
}

?>



<div class="row">
    
    
    <div class="medium-8 columns">
    
    <?php
    // Define Status Overview Class
    $status_overview_class = status_overview(($num_ri_devices/$num_ri_devices_down)*100);
    
    // Residential Infrastructure Table
    echo "<h4><i class='flaticon-technology-1'></i>Residential Infrastructure</h4>";
    echo "<span class='status-overview {$status_overview_class}'>{$num_ri_devices}/{$num_ri_devices_down} Devices Online</span>"; ?>
    
    <table>
        <thead>
            <tr>
                <th class="show-for-large">House</th>
                <th>Device</th>
                <th class="hide-for-small-only">Uptime</th>
                <th class="show-for-large">Downtime</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            
            <?php
            $residence_counter = 0; // Set Residence Counter
            foreach ($residential_infrastructure->group as $key => $residence) {
                $residence_class = $residence_counter % 2 == 0 ? 'even_cell' : 'odd_cell';
                $device_counter = 0; // Reset Counter on each Residence
                
                echo '<tr>';
                    echo "<td class='{$residence_class} show-for-large' rowspan='2'>" . $residence->name . '</td>';
                
                foreach ($residence->device as $device) {
                    echo $device_counter == 0 ? '' : '</tr>';
                        echo '<td>' . $device->name . '</td>';
                        
                        foreach ($device->sensor as $sensor) {
                            if ($sensor->name == 'PING') {
                                // Device Uptime
                                $uptime = number_format((($sensor->cumulateduptime_raw)/60)/60);
                                echo '<td class="hide-for-small-only">' . $uptime . ' Hours</td>';
                                
                                // Device Downtime
                                $downtime = number_format((($sensor->cumulateddowntime_raw)/60));
                                echo '<td class="show-for-large">' . $downtime . ' Minutes</td>';
                                
                                // Device Status
                                $sensor_status_class = $sensor->status == 'Up' ? 'success-status' : 'alert-status';
                                echo "<td class='{$sensor_status_class}'>" . $sensor->status . '</td>';
                            }
                        }
                    
                    echo '</tr>';
                    
                    $device_counter++;
                }
                $residence_counter++;
            }
            ?>
            
        </tbody>
    </table>
    
        
    </div>
    
    
    

</div>