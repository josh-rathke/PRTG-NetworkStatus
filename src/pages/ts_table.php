<div id="ts" data-magellan-target="ts">
    <h4><i class='flaticon-technology-2'></i>Telephony Services</h4>
    <span class='status-overview <?php echo status_overview($ts['percent_ts_devices_up']); ?>'>
        <?php echo $ts['num_ts_devices'] . '/' . $ts['num_ts_devices_up'] . ' Devices Online'; ?>
    </span>
    
    <table>
        <thead>
            <th>IP Phone Name</th>
            <td>Extension</td>
            <th style="text-align: center;">Voicemail</th>
            <th style="text-align: center;">Status</th>
        </thead>
        <tbody>

            <?php
                foreach ($ts['phones'] as $phone) {
                    echo '<tr>';
                        echo '<td>' . $phone['phone_name'] . '</td>';
                        echo '<td>' . $phone['extension'] . '</td>';
                        echo '<td style="text-align: center;">';
                            echo $phone['voicemail'] == 1 ? "<i class='status-icon flaticon-checkmark-outlined-circular-button'></i>" : "";
                        echo '</td>';
                        echo '<td class="' . status_overview($phone['phone_status']) . '">' . $phone['phone_status'] . '</td>';
                    echo '</tr>';
                }
            ?>

        </tbody>
    </table>
</div>