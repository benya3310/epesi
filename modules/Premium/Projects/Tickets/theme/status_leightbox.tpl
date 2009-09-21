<center>
<br>
{$form_open}
<table id="CRM_Filters" cellspacing="0" cellpadding="0" style="width:70%">
	<tr>
        <td style="width:100px;background-color:#336699;border-bottom:1px solid #B3B3B3;color:#FFFFFF;padding-left:5px;padding-right:5px;text-align:left;vertical-align:middle;">
        	{$form_resolution.label}
		</td>
		<td style="width:1px;">
			{$form_resolution.html}
		</td>
		<td colspan="2" style="color:red;padding-left:5px;text-align:left;">
			{$resolution_required_error}
        </td>
	</tr>
	<tr>
        <td style="background-color:#336699;border-bottom:1px solid #B3B3B3;color:#FFFFFF;padding-left:5px;padding-right:5px;text-align:left;vertical-align:middle;">
        	{$form_note.label}
		</td>
		<td colspan="3"><div class="premium_tickets_leightbox_note">
			{$form_note.html}</div>
        </td>
	</tr>
</table>
        <!-- MY -->
<table id="CRM_Filters" cellspacing="0" cellpadding="0">
	<tr>
        <td>

<!-- SHADIW BEGIN -->
	<div class="layer" style="padding: 8px; width: 110px;">
		<div class="content_shadow">
<!-- -->

	    {$reopen.open}
		<div class="big-button">
            <img src="{$theme_dir}/Premium/Projects/Tickets/reopen.png" alt="" align="middle" border="0" width="32" height="32">
            <div style="height: 5px;"></div>
            <span>{$reopen.text}</span>
        </div>
	    {$reopen.close}


<!-- SHADOW END -->
 		</div>
		<div class="shadow-top">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-middle">
			<div class="left"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-bottom">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
	</div>
<!-- -->

        </td>
        <td>

<!-- SHADIW BEGIN -->
	<div class="layer" style="padding: 8px; width: 110px;">
		<div class="content_shadow">
<!-- -->

	    {$resolve.open}
		<div class="big-button">
            <img src="{$theme_dir}/Premium/Projects/Tickets/resolved.png" alt="" align="middle" border="0" width="32" height="32">
            <div style="height: 5px;"></div>
            <span>{$resolve.text}</span>
        </div>
	    {$resolve.close}


<!-- SHADOW END -->
 		</div>
		<div class="shadow-top">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-middle">
			<div class="left"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-bottom">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
	</div>
<!-- -->

        </td>
        <td>

<!-- SHADIW BEGIN -->
	<div class="layer" style="padding: 8px; width: 110px;">
		<div class="content_shadow">
<!-- -->

	    {$feedback.open}
		<div class="big-button">
	        <img src="{$theme_dir}/Premium/Projects/Tickets/feedback.png" alt="" align="middle" border="0" width="32" height="32">
	        <div style="height: 5px;"></div>
	        <span>{$feedback.text}</span>
        </div>
	    {$feedback.close}

<!-- SHADOW END -->
 		</div>
		<div class="shadow-top">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-middle">
			<div class="left"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-bottom">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
	</div>
<!-- -->

        </td>

        <!-- ALL -->
        <td>

<!-- SHADIW BEGIN -->
	<div class="layer" style="padding: 8px; width: 110px;">
		<div class="content_shadow">
<!-- -->

	    {$close.open}
		<div class="big-button">
            <img src="{$theme_dir}/Premium/Projects/Tickets/closed.png" alt="" align="middle" border="0" width="32" height="32">
            <div style="height: 5px;"></div>
            <span>{$close.text}</span>
        </div>
	    {$close.close}

<!-- SHADOW END -->
 		</div>
		<div class="shadow-top">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-middle">
			<div class="left"></div>
			<div class="right"></div>
		</div>
		<div class="shadow-bottom">
			<div class="left"></div>
			<div class="center"></div>
			<div class="right"></div>
		</div>
	</div>
<!-- -->

        </td>
    </tr>
</table>
{$form_close}

</center>
