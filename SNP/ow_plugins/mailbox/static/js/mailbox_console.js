
OW_MailboxConsole = function( itemKey, params )
{
    var listLoaded = false;

    var model = OW.Console.getData(itemKey);
    var list = OW.Console.getItem(itemKey);
    var counter = new OW_DataModel();

    counter.addObserver(this);

    this.onDataChange = function( data )
    {
        var counterNumber = 0,
        newCount = data.get('counter.new');
        counterNumber = newCount > 0 ? newCount : data.get('counter.all');

        list.setCounter(counterNumber, newCount > 0);

        if ( counterNumber > 0 )
        {
            list.showItem();
        }
    };

    list.onHide = function()
    {
        list.setCounter(counter.get('all'), false);
        list.getItems().removeClass('ow_console_new_message');

        model.set('counter', counter.get());
    };

    list.onShow = function()
    {
        if ( params.issetMails == false && counter.get('all') <= 0 )
        {
            this.showNoContent();

            return;
        }
        
        if ( counter.get('new') > 0 && listLoaded || !listLoaded )
        {
            this.loadList();
            listLoaded = true;
        }
    };

    model.addObserver(function()
    {
        if ( !list.opened )
        {
            counter.set(model.get('counter'));
        }
    });
}

OW.MailboxConsole = null;