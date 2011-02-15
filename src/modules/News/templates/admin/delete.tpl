{gt text='Delete news article' assign='templatetitle'}
{admincategorymenu}
<div class="z-adminbox">
    <h1>{$modinfo.displayname}</h1>
    {modulelinks modname='News' type='admin'}
</div>

<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="delete" size="large"}</div>
    <h2>{$templatetitle}</h2>
    <p class="z-warningmsg">{gt text='Do you really want to delete this news article?'}</p>
    {notifydisplayhooks eventname='news.hook.articles.ui.delete' area='modulehook_area.news.articles' subject=$item id=$sid caller="News"}
    <form class="z-form" action="{modurl modname='News' type='admin' func='delete'}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name='generateauthkey' module='News'}" />
            <input type="hidden" name="confirmation" value="1" />
            <input type="hidden" name="sid" value="{$sid|safetext}" />
            <fieldset>
                <legend>{gt text='Confirmation prompt'}</legend>
                <div class="z-formbuttons z-buttons">
                    {button class="z-btgreen" src=button_ok.png set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="z-btred" href="{modurl modname='News' type='admin' func='view'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel'  __title='Cancel'} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>
