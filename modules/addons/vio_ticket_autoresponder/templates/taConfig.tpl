{if $show_message}
    <div class="alert alert-success">Configuration updated successfully</div>
{/if}

<form method="POST">
    <div class="form-group">
        <label for="holidays_email">Holiday Email Template:</label>
        <select name="holidays_email" class="form-control" id="holidays_email">
            <option value="">None</option>
            {html_options options= $emailTemplates selected=$config.holidays_email}
        </select>
    </div>
    <div class="form-group">
        <label for="closed_email">Office Closed Email Template:</label>
        <select name="closed_email" class="form-control" id="closed_email">
            <option value="">None</option>
            {html_options options= $emailTemplates selected=$config.closed_email}
        </select>
    </div>
    <div class="col-sm-12">
        <br /><br />
        <p>Weekend days: </p>
        <div class="col-sm-1">
            <label><input type="checkbox" name="weekend[]" value="Sun" {if 'Sun'|in_array:$config.weekend}checked {/if}><br />Sunday</label>
        </div>
        <div class="col-sm-1">
            <label><input type="checkbox" name="weekend[]" value="Mon" {if 'Mon'|in_array:$config.weekend}checked {/if}><br />Monday</label>
        </div>
        <div class="col-sm-1">
            <label><input type="checkbox" name="weekend[]" value="Tue" {if 'Tue'|in_array:$config.weekend}checked {/if}><br />Tuesday</label>
        </div>
        <div class="col-sm-1">
            <label><input type="checkbox" name="weekend[]" value="Wed" {if 'Wed'|in_array:$config.weekend}checked {/if}><br />Wednesday</label>
        </div>
        <div class="col-sm-1">
            <label><input type="checkbox" name="weekend[]" value="Thu" {if 'Thu'|in_array:$config.weekend}checked {/if}><br />Thursday</label>
        </div>
        <div class="col-sm-1">
            <label><input type="checkbox" name="weekend[]" value="Fri" {if 'Fri'|in_array:$config.weekend}checked {/if}><br />Friday</label>
        </div>
        <div class="col-sm-1">
            <label><input type="checkbox" name="weekend[]" value="Sat" {if 'Sat'|in_array:$config.weekend}checked {/if}><br />Saturday</label>
        </div>
    </div>
    <div class="col-sm-12">
        <br /><br />
        <p>Working hours</p>
        <p>
            From: 
            <input name="from_hours" size="3" value="{$config.from_hours}" />
            : 
            <input name="from_mins" size="3" value="{$config.from_mins}" />
            <select name="from_ampm">
                <option value="am">AM&nbsp;&nbsp;</option>
                <option value="pm" {if $config.from_ampm eq 'pm'}selected {/if}>PM&nbsp;&nbsp;</option>
            </select>
            to
            <input name="to_hours" size="3" value="{$config.to_hours}" />
            : 
            <input name="to_mins" size="3" value="{$config.to_mins}" />
            <select name="to_ampm">
                <option value="am">AM&nbsp;&nbsp;</option>
                <option value="pm" {if $config.to_ampm eq 'pm'}selected {/if}>PM&nbsp;&nbsp;</option>
            </select>
        </p>
    </div>
    <div class="col-sm-12">
        <br /><br />
        <p>Holidays:</p>
        <div id="holidays"></div>
        <input type="hidden" name="holidays" id="closedDates" value='{$config.holidays}' />
    </div>
    <div class="col-sm-12 text-center">
        <br /><br />
        <input value="Save" class="button btn btn-default" type="submit" />
    </div>
</form>
<style>
    .ui-state-highlight a {
        color: #FFF !important;
        background: #1a4d80 !important;
    }
</style>

<script type="text/javascript">
    {literal}

// Maintain array of dates
        var closedDates = $('#closedDates').val();
        if (closedDates.length) {
            var dates = JSON.parse($('#closedDates').val());
        } else {
            var dates = [];
        }

        function addDate(date) {
            if (jQuery.inArray(date, dates) < 0) {
                dates.push(date);
                populateDates();
            }
        }

        function populateDates() {
            $('#closedDates').val(JSON.stringify(dates));
        }

        function removeDate(index) {
            dates.splice(index, 1);
            populateDates();
        }

// Adds a date if we don't have it yet, else remove it
        function addOrRemoveDate(date) {
            var index = jQuery.inArray(date, dates);
            if (index >= 0)
                removeDate(index);
            else
                addDate(date);
        }

// Takes a 1-digit number and inserts a zero before it
        function padNumber(number) {
            var ret = new String(number);
            if (ret.length == 1)
                ret = "0" + ret;
            return ret;
        }

        $(function () {
            $('#holidays').datepicker({
                numberOfMonths: [4, 4],
                minDate: 0,
                onSelect: function (dateText, inst) {
                    addOrRemoveDate(dateText);
                },
                beforeShowDay: function (date) {
                    var year = date.getFullYear();
                    // months and days are inserted into the array in the form, e.g "01/01/1999", but here the format is "1/1/1999"
                    var month = padNumber(date.getMonth() + 1);
                    var day = padNumber(date.getDate());
                    // This depends on the datepicker's date format
                    var dateString = month + "/" + day + "/" + year;
                    var gotDate = jQuery.inArray(dateString, dates);
                    var day = date.getDay();
                    var ret = true;//(day != 0);
                    if (gotDate >= 0) {
                        // Enable date so it can be deselected. Set style to be highlighted
                        return [ret, "ui-state-highlight"];
                    }
                    // Dates not in the array are left enabled, but with no extra style
                    return [ret, ""];
                }
            });
        });
    {/literal}
</script>