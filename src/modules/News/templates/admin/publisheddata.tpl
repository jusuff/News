<b>{gt text='Publication Data for #%s:' tag1=$newsitem.sid}</b>
{if $newsitem.published_status neq 0}
<p class="z-icon-es-flag"><em>{gt text='Article unpublished'} ({$newsitem.status})</em></p>
{/if}
{gt text='forever' assign='forever'}
<ul>
    <li><b>{gt text='Approved/Rejected By'}:</b> {if $newsitem.approver gt 0}{usergetvar uid=$newsitem.approver name='uname'} ({$newsitem.approver}){else}<em>{gt text='Pending'}</em>{/if}</li>
    <li><b>{gt text='Created By'}:</b> {usergetvar uid=$newsitem.cr_uid name='uname'} ({$newsitem.cr_uid}) <b>{gt text='On'}:</b> {$newsitem.cr_date}</li>
    <li><b>{gt text='Last Updated By'}:</b> {usergetvar uid=$newsitem.lu_uid name='uname'} ({$newsitem.lu_uid}) <b>{gt text='On'}:</b> {$newsitem.lu_date}</li>
    <li><b>{gt text='Publish From'}:</b> {$newsitem.from} <b>{gt text='To'}:</b> {$newsitem.to|default:$forever}</li>
</ul>