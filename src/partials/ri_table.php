<?php
// Define Status Overview Class
$status_overview_class = status_overview(($num_ri_devices_up/$num_ri_devices)*100);

// Residential Infrastructure Table
echo "<h4><i class='flaticon-technology-1'></i>Residential Infrastructure</h4>";
echo "<span class='status-overview {$status_overview_class}'>{$num_ri_devices}/{$num_ri_devices_up} Devices Online</span>"; ?>

<table data-magellan>
    <thead>
        <tr>
            <th class="show-for-large">House</th>
            <th>Device</th>
            <th class="hide-for-small-only">Uptime</th>
            <th class="hide-for-small-only">Downtime</th>
            <th class="status-title">Status</th>
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
                    $device->name = tip_lookup($device->name);

                    echo '<td>' . $device->name . '</td>';

                    foreach ($device->sensor as $sensor) {
                        if ($sensor->name == 'PING') {
                            // Device Uptime
                            $uptime = number_format((($sensor->cumulateduptime_raw)/60)/60);
                            echo '<td class="hide-for-small-only">' . $uptime . ' Hours</td>';

                            // Device Downtime
                            $downtime = number_format((($sensor->cumulateddowntime_raw)/60));
                            echo '<td class="hide-for-small-only">' . $downtime . ' Minutes</td>';

                            // Device Status
                            echo "<td class='" . status_class($sensor->status) . "'>" . $sensor->status . '</td>';
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