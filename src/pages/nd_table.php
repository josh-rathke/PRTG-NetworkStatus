<?php

    /** Calculate Total Wireless Backhaul Devices
     *  Subtract Residential Infrastructure from Wireless Backhauls since they are already displayed in
     *  Residential Infrastructure secion.
     *
     */
    $num_nd_devices = $nd['num_nd_devices'] - $nd['num_ri_wb_devices'];
    $num_nd_devices_up = $nd['num_nd_devices_up'] - $nd['num_ri_wb_devices_up'];
    $percent_nd_devices_up = ($num_nd_devices_up / $num_nd_devices)*100

?>


<div id="nd" data-magellan-target="nd">
    <h4><i class='flaticon-connection-1'></i>Network Distribution</h4>
    <span class='status-overview <?php echo status_overview($percent_nd_devices_up); ?>'>
        <?php echo $num_nd_devices_up . '/' . $num_nd_devices . ' Devices Online'; ?>
    </span>
    
    <table>
        <thead>
            <tr>
                <th>Switch Name</th>
                <th class="hide-for-small-only">Response Time</th>
                <th width="13%" class="status-title">Status</th>
            </tr>
        </thead>    
        <tbody>
            <?php
                foreach ($nd['switches'] as $switch) {
                    echo '<tr>';
                        echo '<td>' . tip_lookup($switch['switch_name']) . '</td>';
                        echo '<td class="hide-for-small-only">' . $switch['switch_latency'] . '</td>';
                        echo "<td class='" . status_overview($switch['switch_status']) . "'>" . $switch['switch_status'] . '</td>';
                    echo '</tr>';
                }
            ?>
        </tbody>
    </table>
    
    <table>
        <thead>
            <tr>
                <th>Wireless Backhaul Name</th>
                <th>Response Time</th>
                <th style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($nd['wb_devices'] as $wb) {
                echo '<tr>';
                    echo '<td>' . $wb['device_name'] . '</td>';
                    echo '<td>' . $wb['device_latency'] . '</td>';
                    echo '<td class="' . status_overview($wb['device_status']) . '">' . $wb['device_status'] . '</td>';
                echo '</tr>';
            }
            
            
            ?>
        </tbody>
    </table>
</div>