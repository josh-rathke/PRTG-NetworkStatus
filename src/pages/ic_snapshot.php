<table class="snapshot-container">
    <thead>
        <tr class="snapshot-title">
            <th class="snapshot-logo"><i class='flaticon-technology-3'></i></th>
            <th><h6>Internet Connectivity</h6></th>
            <th class="snapshot-overview <?php echo status_overview($percent_wan_connections_up); ?>"><div><?php echo $num_wan_connections_up . '/' . $num_wan_connections; ?></div></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="3">
                <div class="ic-snapshot-traffic <?php echo status_overview($percent_wan_connections_up); ?>">
                    <div class="ic-snapshot-traffic-title">Traffic</div>
                    <div class="ic-snapshot-traffic-total"><?php echo $total_wan_traffic; ?></div>
                    <div class="ic-snapshot-traffic-unit">Mbit/s</div>
                </div>
            </td>
        </tr>

        <?php foreach ($wan_connections as $connection) {
            echo '<tr>';
                echo '<td colspan="2">' . $connection->name . '</td>';
                echo '<td class="' . status_overview($connection->status) . '">' . $connection->status . '</td>';
            echo '</td>';

        } ?>
    </tbody>
</table>