<div class="vem-one-occurrence closed">
    <button type="button" class="vem-toggle-button"><span class="screen-reader-text">Toggle panel: <?= $data['headerTitle'] ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>
    <h3 class="vem-header-bar"><?= $data['headerTitle'] ?></h3>
    <div class="vem-form">
        <div class="vem-form-wrapper">
            <?= $data['hiddenIdField'] ?>
            <div class="vem-form-core">
                <div class="vem-form-details">
                    <h3>Details</h3>
                    
                    <label>Venue</label>
                    <select name="vem_dates[<?= $data['i'] ?>][vem_dates_venue_id]" class="vem-venue">
                    <?php foreach ($data['venues'] as $value => $name) {
                        echo '<option value="' . $value . '"' . (($data['venue'] == $value) ? ' selected="selected"' : null) . '>' . $name . '</option>';
                    }?>
                    </select>

                    <label>Note</label>
                    <textarea name="vem_dates[<?= $data['i'] ?>][vem_dates_note]" class="vem-dates-note" rows="3"><?= $data['note'] ?></textarea>
                </div>
                <div class="vem-form-terms">
                    <h3>Categories</h3>
                    <label>Select Categories</label>
                    <div class="taxonomydiv vem-form-terms-list">
                        <ul class="categorychecklist">
                        <?= $data['displayTerms'] ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="vem-form-scheduling">
                <div class="vem-form-schedule">
                    <h3>Schedule</h3>
                    <label>Date</label>
                    <input class="vem-dates-date date-picker" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_date]" value="<?= $data['displayDate'] ?>">

                    <label>Start Time</label>
                    <input class="vem-dates-start_time vem-dates-time" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_start_time][hour]" value="<?= $data['displayStartTime'] ?>">
                    <select name="vem_dates[<?= $data['i'] ?>][vem_dates_start_time][meridian]" class="vem-dates-meridian vem-dates-meridian-from">
                        <option value="AM"' <?= (($data['displayStartMeridian'] == 'AM') ? ' selected="selected"' : null) ?>>AM</option>
                        <option value="PM"' <?= (($data['displayStartMeridian'] == 'PM') ? ' selected="selected"' : null) ?>>PM</option>
                    </select>

                    <label>End Time</label>
                    <input class="vem-dates-end_time vem-dates-time" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_end_time][hour]" value="<?= $data['displayEndTime'] ?>">
                    <select name="vem_dates[<?= $data['i'] ?>][vem_dates_end_time][meridian]" class="vem-dates-meridian vem-dates-meridian-to">
                        <option value="AM"' <?= (($data['displayEndMeridian'] == 'AM') ? ' selected="selected"' : null) ?>>AM</option>
                        <option value="PM"' <?= (($data['displayEndMeridian'] == 'PM') ? ' selected="selected"' : null) ?>>PM</option>
                    </select>
                </div>

                <div class="vem-form-gsd">
                    <div class="vem-form-gsd-status">
                        <h3>Google Structured Data</h3>
                        <label>Event Status</label>
                        <select class="vem-dates-gsd-status" name="vem_dates[<?= $data['i'] ?>][vem_dates_gsd_status]">
                            <option value="EventScheduled" <?= (($data['gsd_status'] == 'EventScheduled') ? ' selected="selected"' : null) ?>>Scheduled</option>
                            <option value="EventPostponed" <?= (($data['gsd_status'] == 'EventPostponed') ? ' selected="selected"' : null) ?>>Postponed</option>
                            <option value="EventRescheduled" <?= (($data['gsd_status'] == 'EventRescheduled') ? ' selected="selected"' : null) ?>>Rescheduled</option>
                            <option value="EventMovedOnline" <?= (($data['gsd_status'] == 'EventMovedOnline') ? ' selected="selected"' : null) ?>>Moved Online</option>
                            <option value="EventCancelled" <?= (($data['gsd_status'] == 'EventCancelled') ? ' selected="selected"' : null) ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="vem-dates-gsd-detail vem-dates-gsd-eventrescheduled"<?= (($data['gsd_status'] == 'EventRescheduled') ? ' style="display:block;"' : null) ?>>
                        <label>Previously Scheduled Date</label>
                        <input class="vem-dates-date date-picker" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_gsd_previous_date]" value="<?= $data['displayPreviousDate'] ?>">

                        <label>Previously Scheduled Time</label>
                        <input class="vem-dates-start_time vem-dates-time" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_gsd_previous_start_time][hour]" value="<?= $data['displayPreviousTime'] ?>">
                        <select name="vem_dates[<?= $data['i'] ?>][vem_dates_gsd_previous_start_time][meridian]" class="vem-dates-gsd-meridian vem-dates-gsd-meridian-from">
                            <option value="AM"' <?= (($data['displayPreviousMeridian'] == 'AM') ? ' selected="selected"' : null) ?>>AM</option>
                            <option value="PM"' <?= (($data['displayPreviousMeridian'] == 'PM') ? ' selected="selected"' : null) ?>>PM</option>
                        </select>
                    </div>

                    <div class="vem-dates-gsd-detail vem-dates-gsd-eventpostponed"<?= (($data['gsd_status'] == 'EventPostponed') ? ' style="display:block;"' : null) ?>>
                        <p>Use <strong>Postponed</strong> when the event will be delayed but a new schedule isn't yet set.
                        Once a new schedule has been set, change this to <strong>Rescheduled</strong>.</p>
                        <p>When applicable, include additional information in the <strong>Note</strong> field.</p>
                    </div>

                    <div class="vem-dates-gsd-detail vem-dates-gsd-eventmovedonline"<?= (($data['gsd_status'] == 'EventMovedOnline') ? ' style="display:block;"' : null) ?>>
                        <label>URL for Online Event</label>
                        <input class="vem-dates-gsd_url" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_gsd_url]" value="<?= $data['gsd_url'] ?>">
                        <p>Events moved online are expected to include a URL for the virtual location, which will be used in place of the venue.</p>
                    </div>

                    <div class="vem-dates-gsd-detail vem-dates-gsd-eventcancelled"<?= (($data['gsd_status'] == 'EventCancelled') ? ' style="display:block;"' : null) ?>>
                        <p><strong>Cancelled</strong> events are expected to keep the original event information, so don't change the information above.</p>
                    </div>
                </div>
            </div>

            <div class="vem-form-ticketing">
                <div class="vem-form-ticket1">
                    <h3>First Ticket</h3>

                    <label>Button Text</label>
                    <input class="vem-dates-ticket_button_text" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_ticket_button_text]" value="<?= $data['buytext'] ?>">

                    <label>From Price (<?= $data['defaultCurrencySymbol'] ?>)</label>
                    <input class="vem-dates-ticket_price_from" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_ticket_price_from]" value="<?= $data['ticket_price_from'] ?>">

                    <label>To Price (<?= $data['defaultCurrencySymbol'] ?>)</label>
                    <input class="vem-dates-ticket_price_to" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_ticket_price_to]" value="<?= $data['ticket_price_to'] ?>">

                    <label>URL</label>
                    <input class="vem-dates-ticket_url" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_ticket_url]" value="<?= $data['tickets'] ?>">
                </div>
                <div class="vem-form-ticket2">
                    <h3>Second Ticket</h3>

                    <label>Button Text</label>
                    <input class="vem-dates-ticket2_button_text" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_ticket2_button_text]" value="<?= $data['buytext2'] ?>">

                    <label>From Price (<?= $data['defaultCurrencySymbol'] ?>)</label>
                    <input class="vem-dates-ticket2_price_from" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_ticket2_price_from]" value="<?= $data['ticket2_price_from'] ?>">

                    <label>To Price (<?= $data['defaultCurrencySymbol'] ?>)</label>
                    <input class="vem-dates-ticket2_price_to" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_ticket2_price_to]" value="<?= $data['ticket2_price_to'] ?>">

                    <label>URL</label>
                    <input class="vem-dates-ticket2_url" type="text" name="vem_dates[<?= $data['i'] ?>][vem_dates_ticket2_url]" value="<?= $data['tickets2'] ?>">
                </div>
            </div>

        </div>
        <div class="vem-remove-bar"><button class="button button-secondary vem-remove">Remove</button></div>
    </div>
</div>
