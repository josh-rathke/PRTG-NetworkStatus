<?php
    
// Define Status Overview Class
$status_overview_class = status_overview(($num_wan_connections_up/$num_wan_connections)*100);

// Residential Infrastructure Table
echo "<h4><i class='flaticon-technology-3'></i>Internet Connectivity</h4>";
echo "<span class='status-overview {$status_overview_class}'>{$num_wan_connections}/{$num_wan_connections_up} Connections Available</span>"; ?>

<table>
    <thead>
        <tr>
            <th>Connection</th>
            <th class="hide-for-small-only">Current Traffic</th>
            <th class="status-title">Status</th>
        </tr>
    </thead>    
    <tbody>
        <?php
            foreach ($core_router->sensor as $sensor) {
                if (strpos($sensor->name, 'WAN') !== false) {
                    echo '<tr>';
                        echo '<td>' . tip_lookup($sensor->name) . '</td>';
                        echo '<td class="hide-for-small-only">' . $sensor->lastvalue . '</td>';
                        echo "<td class='" . status_overview($sensor->status) . "'>" . $sensor->status . '</td>';
                    echo '</tr>';
                }
            }
        ?>
    </tbody>
</table>