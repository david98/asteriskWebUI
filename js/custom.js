var asteriskWebUI = function () {

    /*
     *  phoneRegEx: espressione regolare per controllare la validità di un numero telefonico
     *  ipRegEx: espressione regolare per controllare la validità di un indirizzo IP
     *  domainNameRegEx: espressione regolare per controllare la validità di un nome di dominio
    */
    
    var phoneRegEx = /^([0-9]*\-?\ ?\/?[0-9]*)$/,
        ipRegEx = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/,
        domainNameRegEx = /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/;
    
    /*
     *  currentPage: riferimento al container della pagina
     *  pageTitle: riferimento al titolo della paginas
     *  menuButtons: array contenente dei riferimenti a tutte gli elementi <li> del menu laterale
     *  oldContent: contiene un riferimento all'elemento <a class="list-group-item"> su cui è stato aperto il form di modifica nella pagina "Rubrica"   
     *  oldHTML: stringa che contiene il markup HTML di oldContent
     *  loadStatusBtn, loadContactsBtn, loadSipAccountsSettingsBtn, loadAsteriskSettingsBtn, loadExtensionsSettingsBtn, saveSettingsBtn, undoSettingsBtn, loadLogsBtn: riferimenti alle voci <li> nel menu laterale
     *  telNum: riferimento al campo input nel form del dialers   
	 *  dialerBtns: riferimento ai bottoni del dialer
    */
    
    var currentPage = null,
    	pageTitle = null,
        menuButtons = [],
        oldContent = null,
        oldHTML = '',
        loadStatusBtn = null,
        loadContactsBtn = null,
        loadSipAccountsSettingsBtn = null,
        loadAsteriskSettingsBtn = null,
        loadExtensionsSettingsBtn = null,
        saveSettingsBtn = null,
        undoSettingsBtn = null,
        loadLogsBtn = null,
        telNum = null,
        dialerBtns = null;
    
    /*
     *  Aggiunge il selettore "containsIN", che funziona come :contains ma è case INsensitive       
    */
    $.extend($.expr[":"], {
        "containsIN": function(elem, i, match, array) {
            return (elem.textContent || elem.innerText || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
        }
    });
    
    /*
     * metodo per portare il cursore al termine di un input 
    */
    jQuery.fn.putCursorAtEnd = function() {
    
      return this.each(function() {
        
        // Cache references
        var $el = $(this),
            el = this;
    
        // Only focus if input isn't already
        if (!$el.is(":focus")) {
         $el.focus();
        }
    
        // If this function exists... (IE 9+)
        if (el.setSelectionRange) {
    
          // Double the length because Opera is inconsistent about whether a carriage return is one character or two.
          var len = $el.val().length * 2;
          
          // Timeout seems to be required for Blink
          setTimeout(function() {
            el.setSelectionRange(len, len);
          }, 1);
        
        } else {
          
          // As a fallback, replace the contents with itself
          // Doesn't work in Chrome, but Chrome supports setSelectionRange
          $el.val($el.val());
          
        }
    
        // Scroll to the bottom, in case we're in a tall textarea
        // (Necessary for Firefox and Chrome)
        this.scrollTop = 999999;
    
      });
    
    };
    
    /*
     * rimuove la classe 'active' da tutti gli elementi presenti nell'array menuButtons 
    */
    function disableMenuVoices(){
        for( var i = 0; i < menuButtons.length; i++ )
            menuButtons[i].removeClass('active');
    }
    
    /*
     * controlla la validità dei dati inseriti nel form di login
    */ 
    function checkLoginForm()
    {
        //controllare il form di login
    }    
    
    function submitAccountForm(id)
    {   
        var readyToGo = true;
        var pwdValue = $('#newPassword' + id).val();
        var domainValue = $('#newDomain' + id).val();
        var ringsOnValue = $('#newRingsOn' +id).val();
        
        
        if( pwdValue.length >= 0 )
            $('#newPassword' + id).parent().parent().removeClass('has-error').addClass('has-success');
        else
        {
            $('#newPassword' + id).parent().parent().removeClass('has-success').addClass('has-error');
            readyToGo = false;
        }
        
        if( ipRegEx.test(domainValue) ||  domainNameRegEx.test(domainValue) || domainValue === "" )
            $('#newDomain'+ id).parent().parent().removeClass('has-error').addClass('has-success'); 
        else
        {
            $('#newDomain'+ id).parent().parent().removeClass('has-success').addClass('has-error');
            readyToGo = false;
        }
        
        if( readyToGo )
        {
            $.post('configs/accounts.php', {phone: id, password: pwdValue, domain: domainValue, ringsOn: ringsOnValue}, function(data){
                currentPage.html('<button type="button" class="btn btn-success" onclick="$(\'#newAccountFormContainer\').toggleClass(\'hidden\')">Aggiungi un account SIP</button><br/><br/><div id="newAccountFormContainer" class="box box-solid box-default hidden"> <div class="box-header"> <h3 class="box-title">Inserisci i dati</h3> </div><div class="box-body"> <form class="form-horizontal" onsubmit="submitNewAccountForm()"> <div class="form-group"> <label for="newAccountUsername" class="col-sm-2 control-label">Username</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newAccountUsername" placeholder="Username"> </div></div><div class="form-group"> <label for="newAccountPassword" class="col-sm-2 control-label">Password</label> <div class="col-sm-10"> <input type="password" class="form-control" id="newAccountPassword" placeholder="Password"> </div></div><div class="form-group"> <label for="newAccountPhoneNumber" class="col-sm-2 control-label">Numero</label> <div class="col-sm-10"> <input type="tel" class="form-control" id="newAccountPhoneNumber" placeholder="Numero"> <span class="help-block">Inserire il numero di telefono associato a questo account</span> </div></div><div class="form-group"> <label for="newAccountDomain" class="col-sm-2 control-label">Dominio</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newAccountDomain" placeholder="Dominio o indirizzo IP"> <span class="help-block">Inserire un indirizzo IP nel formato XXX.XXX.XXX.XXX (es: 192.168.1.1) o un nome di dominio</span> </div></div><div class="form-group"> <div class="col-sm-offset-2 col-sm-10"> <button type="submit" class="btn btn-default">Conferma</button> </div></div></form> </div></div>' + data);
            });
    
        }
    
    }
    
    function removeAccount(id)
    {
        $.post('configs/accounts.php', { removeAccount: id }, function(data){
                currentPage.html('<button type="button" class="btn btn-success" onclick="$(\'#newAccountFormContainer\').toggleClass(\'hidden\')">Aggiungi un account SIP</button><br/><br/><div id="newAccountFormContainer" class="box box-solid box-default hidden"> <div class="box-header"> <h3 class="box-title">Inserisci i dati</h3> </div><div class="box-body"> <form class="form-horizontal" onsubmit="submitNewAccountForm()"> <div class="form-group"> <label for="newAccountUsername" class="col-sm-2 control-label">Username</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newAccountUsername" placeholder="Username"> </div></div><div class="form-group"> <label for="newAccountPassword" class="col-sm-2 control-label">Password</label> <div class="col-sm-10"> <input type="password" class="form-control" id="newAccountPassword" placeholder="Password"> </div></div><div class="form-group"> <label for="newAccountPhoneNumber" class="col-sm-2 control-label">Numero</label> <div class="col-sm-10"> <input type="tel" class="form-control" id="newAccountPhoneNumber" placeholder="Numero"> <span class="help-block">Inserire il numero di telefono associato a questo account</span> </div></div><div class="form-group"> <label for="newAccountDomain" class="col-sm-2 control-label">Dominio</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newAccountDomain" placeholder="Dominio o indirizzo IP"> <span class="help-block">Inserire un indirizzo IP nel formato XXX.XXX.XXX.XXX (es: 192.168.1.1) o un nome di dominio</span> </div></div><div class="form-group"> <div class="col-sm-offset-2 col-sm-10"> <button type="submit" class="btn btn-default">Conferma</button> </div></div></form> </div></div>' + data);
        });
    }
    
    function submitNewAccountForm()
    {
        var readyToGo = true;
        var usernameValue = $('#newAccountUsername').val();
        var pwdValue = $('#newAccountPassword').val();
        var phoneNumberValue = $('#newAccountPhoneNumber').val();
        var domainValue = $('#newAccountDomain').val();
        
        if( usernameValue !== "" )
            $('#newAccountUsername').parent().parent().removeClass('has-error').addClass('has-success'); 
        else
        {
            $('#newAccountUsername').parent().parent().removeClass('has-success').addClass('has-error');
            readyToGo = false;
        }
        
        if( pwdValue !== "" )
            $('#newAccountPassword').parent().parent().removeClass('has-error').addClass('has-success'); 
        else
        {
            $('#newAccountPassword').parent().parent().removeClass('has-success').addClass('has-error');
            readyToGo = false;
        }
        
        if( phoneRegEx.test(phoneNumberValue) )
            $('#newAccountPhoneNumber').parent().parent().removeClass('has-error').addClass('has-success'); 
        else
        {
            $('#newAccountPhoneNumber').parent().parent().removeClass('has-success').addClass('has-error');
            readyToGo = false;
        }
        
        if( ipRegEx.test(domainValue) ||  domainNameRegEx.test(domainValue) )
            $('#newAccountDomain').parent().parent().removeClass('has-error').addClass('has-success'); 
        else
        {
            $('#newAccountDomain').parent().parent().removeClass('has-success').addClass('has-error');
            readyToGo = false;
        }
        
        
        if( readyToGo )
        {
            $.post('configs/accounts.php', {addAccount: phoneNumberValue, username: usernameValue, password: pwdValue, domain: domainValue}, function(data){
                currentPage.html('<button type="button" class="btn btn-success" onclick="$(\'#newAccountFormContainer\').toggleClass(\'hidden\')">Aggiungi un account SIP</button><br/><br/><div id="newAccountFormContainer" class="box box-solid box-default hidden"> <div class="box-header"> <h3 class="box-title">Inserisci i dati</h3> </div><div class="box-body"> <form class="form-horizontal" onsubmit="submitNewAccountForm()"> <div class="form-group"> <label for="newAccountUsername" class="col-sm-2 control-label">Username</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newAccountUsername" placeholder="Username"> </div></div><div class="form-group"> <label for="newAccountPassword" class="col-sm-2 control-label">Password</label> <div class="col-sm-10"> <input type="password" class="form-control" id="newAccountPassword" placeholder="Password"> </div></div><div class="form-group"> <label for="newAccountPhoneNumber" class="col-sm-2 control-label">Numero</label> <div class="col-sm-10"> <input type="tel" class="form-control" id="newAccountPhoneNumber" placeholder="Numero"> <span class="help-block">Inserire il numero di telefono associato a questo account</span> </div></div><div class="form-group"> <label for="newAccountDomain" class="col-sm-2 control-label">Dominio</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newAccountDomain" placeholder="Dominio o indirizzo IP"> <span class="help-block">Inserire un indirizzo IP nel formato XXX.XXX.XXX.XXX (es: 192.168.1.1) o un nome di dominio</span> </div></div><div class="form-group"> <div class="col-sm-offset-2 col-sm-10"> <button type="submit" class="btn btn-default">Conferma</button> </div></div></form> </div></div>' + data);
            });
        }
    }
    
    function submitNewExternalIp()
    {
        var readyToGo = true;
        var ipRegEx = new RegExp("^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$");
        var externalIpValue = $('#externalIP').val();
        
        if( ipRegEx.test(externalIpValue) )
            $('#externalIP').parent().parent().removeClass('has-error').addClass('has-success');
        else
        {
            $('#externalIP').parent().parent().removeClass('has-success').addClass('has-error');
            readyToGo = false;
        }
        
        if( readyToGo )
        {
            currentPage.html('<div class="box box-default"> <div class="box-header with-border"> <h3 class="box-title">IP esterno del server Asterisk</h3> <div class="box-tools pull-right"> <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button> </div></div><div class="box-body"> Potresti volere utilizzare questo IP come IP esterno: </div><div class="overlay"> <i class="fa fa-refresh fa-spin"></i></div></div>');
            $.post('configs/asterisk.php', { newIP: externalIpValue }, function(data){
                currentPage.html(data + '<form onsubmit="submitNewExternalIp()"> <form class="form-horizontal"> <div class="form-group"> <label for="externalIP" class="col-sm-1 control-label">IP esterno</label> <div class="col-sm-4"> <input type="text" class="form-control" id="externalIP" placeholder="Indirizzo IP"> <span class="help-block">Inserire un indirizzo IP nel formato XXX.XXX.XXX.XXX (es: 192.168.1.1)</span> </div></div><div class="form-group"> <div class="col-sm-1"> <button type="submit" class="btn btn-default">Conferma</button> </div></div></form>');
            });
        }
    }
    
    function loadLogs(download)
    {
        $.when($.ajax('configs/logs.php')).then(function(data, textStatus, jqXHR){
            currentPage.html(data);
            $('#log').scrollTop($('#log')[0].scrollHeight);
        });
    }
    
    function submitNewContact(id){
    
        var newContactName = $('#newContactName');
        var newContactSurname = $('#newContactSurname');
        var newContactPhone = $('#newContactPhone');
        
        var readyToGo = true;
        
        if( newContactName.val() !== "" )
            newContactName.parent().parent().removeClass('has-error').addClass('has-success'); 
        else
        {
            newContactName.parent().parent().removeClass('has-success').addClass('has-error');
            readyToGo = false;
        }
        
        if( newContactSurname.val() !== "" )
            newContactSurname.parent().parent().removeClass('has-error').addClass('has-success'); 
        else
        {
            newContactSurname.parent().parent().removeClass('has-success').addClass('has-error');
            readyToGo = false;
        }
        
        if( phoneRegEx.test(newContactPhone.val()) && newContactPhone.val() !== "")
            newContactPhone.parent().parent().removeClass('has-error').addClass('has-success'); 
        else
        {
            newContactPhone.parent().parent().removeClass('has-success').addClass('has-error');
            readyToGo = false;
        }
        
        if( readyToGo )
        {
            $.post('configs/contacts.php', {name: newContactName.val(), surname: newContactSurname.val(), phone: newContactPhone.val()}, function(data){
                loadContacts();
            });
        }
    
    }
    
    function submitEditedContact(context){
        var newName = $('#newName', context).val();
        var newSurname = $('#newSurname', context).val();
        var newPhone = $('#newPhone', context).val();
        var id = $('#contactId', context).val();
        
        var readyToGo = true;
        
        if( newName === "" )
            readyToGo = false;  
        
        if( newSurname === "" )
            readyToGo = false;
        
        if( !(phoneRegEx.test(newPhone) && newPhone !== "") )
            readyToGo = false;
        
        if( readyToGo )
        {
            $.post('configs/contacts.php', {name: newName, surname: newSurname, phone: newPhone, _id: id}, function(data){
                loadContacts();
            });
        }
        else
        {
            //context.parent().css('background-color', '#dd4b39');
            $('.callout', context).remove();
            context.append('<div class="callout callout-danger lead"> <h4>Errore!</h4> <p> Ricontrolla i dati inseriti. </p></div>');
        }
    }
    
    function loadContacts(){
        $.get('configs/contacts.php', function(data){
            currentPage.html('<div class="input-group stylish-input-group"><input type="text" id="searchContact" class="form-control"  placeholder="Cerca" ><span class="input-group-addon"><button type="submit"><span class="glyphicon glyphicon-search"></span></button></span></div><button id="newContact" class="btn btn-success">Nuovo contatto</button><div id="newContactFormContainer" class="box box-solid box-default"> <div class="box-header"> <h3 class="box-title">Inserisci i dati</h3> </div><div class="box-body"> <form class="form-horizontal"> <div class="form-group"> <label for="newContactName" class="col-sm-2 control-label">Nome</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newContactName" placeholder="Mario"> </div></div><div class="form-group"> <label for="newContactSurname" class="col-sm-2 control-label">Cognome</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newContactSurname" placeholder="Rossi"> </div></div><div class="form-group"> <label for="newContactPhone" class="col-sm-2 control-label">Numero</label> <div class="col-sm-10"> <input type="tel" class="form-control" id="newContactPhone" placeholder="Numero"> <span class="help-block">Inserire il numero di telefono associato a questo contatto</span> </div><div class="form-group"> <div class="col-sm-offset-2 col-sm-10"> <button type="submit" class="btn btn-default">Conferma</button> </div></div></form> </div></div></div><div class="list-group">' + data + '</div>');   
            $('.btn-group').hide();
            $('#newContactFormContainer').hide();
            $('#newContactFormContainer form').submit(submitNewContact);
            $('#newContact').off('click').click(function(){
                $('#newContactFormContainer').toggle();
            });
            $('.list-group-item').off('click').click(function(){
                $('.btn-group').hide();
                $('.btn-group', $(this)).toggle();
                $('.btn-group .btn-danger').off('click').click(function(){
                    if( window.confirm("Sicuro di volere rimuovere questo contatto?") )
                        removeContact($(this).parent().attr('id'));
                });
                $('.btn-group .btn-primary').off('click').click(function(){
                    oldContent = $(this).parent();
                    var id = oldContent.attr('id');
                    oldHTML = oldContent.parent().html();
                    
                    var nameSurname = $('h4', oldContent.parent()).text().split(' ');
                    var phone = $('p', oldContent.parent()).text();
                    
                    oldContent.parent().html('<form> <div class="form-group"> <input type="text" class="form-control" id="newName" name="name" placeholder="' + nameSurname[0] + '" value="' + nameSurname[0] + '" onfocus="$(this).putCursorAtEnd();"> </div><div class="form-group"> <input type="text" class="form-control" id="newSurname" name="surname" placeholder="' + nameSurname[1]  + '" value="' + nameSurname[1] + '" onfocus="$(this).putCursorAtEnd();"> </div><div class="form-group"> <input type="tel" class="form-control" id="newPhone" name="phone" placeholder="' + phone + '" value="' + phone + '" onfocus="$(this).putCursorAtEnd();"> </div><input type="hidden" name="_id" value="' + id + '" id="contactId"/> <button type="submit" class="btn btn-default">Conferma</button><button id="undoBtn" class="btn btn-warning">Annulla</button></form>');
                    
                    $('form').submit(function(event){
                        event.preventDefault();
                        submitEditedContact($(this));
                    });
                    
                    $('#undoBtn').click(function(){
                        $(this).parent().parent().html(oldHTML);
                    });
                    
                });
                
                $('.btn-group .btn-success').off('click').click(function(){
                    alert("Chiamata!");
                });
            });
            
            
            $('#searchContact').off('click').on('keyup', function(){
                var searchedText = $(this).val();
                if( searchedText !== "" )
                {
                    $('.list-group-item').hide();
                    $('.list-group-item:containsIN("' + searchedText + '")').show();
                    $('.list-group-item .btn-group:containsIN("' + searchedText + '")').parent().hide();
                }
                else
                    $('.list-group-item').show();
                    
            });
            
        });
    }
    
    function removeContact(id){
        $.post('configs/contacts.php', {_id: id, delete: true}, function(data){
            loadContacts();
        });
    }
    
    function loadExtensions(){
	    $.get('configs/extensions.php', function(data){
                    currentPage.html('<button type="button" class="btn btn-success" onclick="$(\'#newExtensionFormContainer\').toggleClass(\'hidden\')">Aggiungi un\'estensione</button><br/><br/><div id="newExtensionFormContainer" class="box box-solid box-default hidden"> <div class="box-header"> <h3 class="box-title">Inserisci i dati</h3> </div><div class="box-body"> <form id="newExtensionForm" class="form-horizontal"> <div class="form-group"> <label for="newExtensionName" class="col-sm-2 control-label">Nome</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newExtensionName" placeholder="Nome"> </div></div><div class="form-group"> <label for="newExtensionUsername" class="col-sm-2 control-label">Username</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newExtensionUsername" placeholder="Username"> </div></div><div class="form-group"> <label for="newExtensionPassword" class="col-sm-2 control-label">Password</label> <div class="col-sm-10"> <input type="password" class="form-control" id="newExtensionPassword" placeholder="Password"> </div></div><div class="form-group"> <label for="newExtensionIP" class="col-sm-2 control-label">IP</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newExtensionIP" placeholder="Indirizzo IP"> <span class="help-block">Inserire un indirizzo IP nel formato XXX.XXX.XXX.XXX (es: 192.168.1.1)</span> </div></div><div class="form-group"> <div class="col-sm-offset-2 col-sm-10"> <button type="submit" class="btn btn-default">Conferma</button> </div></div></form> </div></div>' + data);
                    
        });
    }
    
    /* HANDLER PER EVENTI */
    
    var handlers = {
	    click: {
			dialerBtns: function(){
			    if( telNum.val().length < telNum.attr('maxlength') )
		        	telNum.val(telNum.val() + $(this).text());
		        window.navigator.vibrate(100);
		    },
		    saveUndoSettingsBtns: function(){
			    disableMenuVoices();
                $(this).addClass('active');
		    },
		    loadContactsBtn: function(){
			    pageTitle.text('Rubrica');
			    document.title = "Asterisk WebUI | Rubrica";
				disableMenuVoices();
				$(this).addClass('active');
				loadContacts();
		    },
		    loadLogsBtn: function(){
		    	pageTitle.text('Logs');
		    	disableMenuVoices();
			    $(this).addClass('active');
			    document.title = "Asterisk WebUI | Log";
				loadLogs();				
		    },
		    loadExtensionsSettingsBtn: function(){
                pageTitle.text('Estensioni');
                document.title = "Asterisk WebUI | Estensioni";
                disableMenuVoices();
                $(this).addClass('active');
                $('#extension-deleted').remove();
                
                loadExtensions();
            },
		    removeExtension: function(event)
		    {
		    	var classes = event.target.className.split(" ");
		    	var id = classes[classes.length - 1];
		        $.post('configs/extensions.php', { removeExtension: id }, function(data){
		                currentPage.html(data);
		        });
		    }     
	    },
	    submit: {
		    noSubmit: function(event){
			    event.preventDefault();
		    },
		    newExtension: function(event){
		    	event.preventDefault();
		    	
		        var readyToGo = true;
		        var ip = $('#newExtensionIP', this);
		        var pwd = $('#newExtensionPassword', this);
		        var username = $('#newExtensionUsername', this);
		        var name = $('#newExtensionName', this);
		        
		        if(!ipRegEx.test(ip.val()))
		        { 
		            ip.parent().parent().removeClass('has-success').addClass('has-error');
		            readyToGo = false;
		        }
		        else
		            ip.parent().parent().removeClass('has-error').addClass('has-success');
		        
		        if( pwd.val().length > 0 )
		            pwd.parent().parent().removeClass('has-error').addClass('has-success');
		        else
		        {
		            pwd.parent().parent().removeClass('has-success').addClass('has-error');
		            readyToGo = false;
		        }
		        
		        if( username.val().length > 0 )
		            username.parent().parent().removeClass('has-error').addClass('has-success');
		        else
		        {
		            username.parent().parent().removeClass('has-success').addClass('has-error');
		            readyToGo = false;
		        }
		        
		        if( name.val().length > 0 )
		            name.parent().parent().removeClass('has-error').addClass('has-success');
		        else
		        {
		            name.parent().parent().removeClass('has-success').addClass('has-error');
		            readyToGo = false;
		        }
		        
		        if( readyToGo )
		        {
		            $.post('configs/extensions.php', { addExtension: name.val(), username: username.val(), password: pwd.val(), ip: ip.val() }, function(data){
		                currentPage.html(data);
		                
		                handlers.click.loadExtensionsSettingsBtn();
		            });
		        }
		    
		    },
		    editExtension: function(event){
		    	event.preventDefault();
		    	var id = event.target.id;
		        var readyToGo = true;
		        var ip = $("#newIP" + id);
		        var pwd = $('#newPassword' + id);
				
		        if(!ipRegEx.test(ip.val()))
		        { 
		            ip.parent().parent().removeClass('has-success').addClass('has-error');
		            readyToGo = false;
		        }
		        else
		            ip.parent().parent().removeClass('has-error').addClass('has-success');
		        
		        if( pwd.val().length > 0 )
		            pwd.parent().parent().removeClass('has-error').addClass('has-success');
		        else
		        {
		            pwd.parent().parent().removeClass('has-success').addClass('has-error');
		            readyToGo = false;
		        }
		        
		        if( readyToGo )
		        {
		            $.post('configs/extensions.php', { name: id, password: pwd.val(), ip: ip.val() }, function(data){
		                $('#' + id).html('<div class="box box-solid box-success"><div class="box-header"><h3 class="box-title">Successo!</h3></div><!-- /.box-header --><div class="box-body">Estensione modificata con successo.</div><!-- /.box-body --></div>');
		                handlers.click.loadExtensionsSettingsBtn()
		            });
		        }
		    
		    }

	    }
    };
    
    $(document).ready(function(){
    
    	currentPage = $('#current-page');
    	pageTitle = $('#page-title');
		
        loadStatusBtn = $('#loadStatus');
        loadContactsBtn = $('#loadContacts');
        loadSipAccountsSettingsBtn = $('#loadSipAccountsSettings');
        loadAsteriskSettingsBtn = $('#loadAsteriskSettings');
        loadExtensionsSettingsBtn = $('#loadExtensionsSettings');
        saveSettingsBtn = $('#saveSettings');
        undoSettingsBtn = $('#undoSettings');
        loadLogsBtn = $('#loadLogs');
        
        telNum = $('#telNum');
        dialerBtns = $('.dialer_btn');
        
        menuButtons.push(loadStatusBtn);
        menuButtons.push(loadContactsBtn);
        menuButtons.push(loadSipAccountsSettingsBtn);
        menuButtons.push(loadAsteriskSettingsBtn);
        menuButtons.push(loadExtensionsSettingsBtn);
        menuButtons.push(saveSettingsBtn);
        menuButtons.push(loadLogsBtn);
        
        currentPage.on('submit', '#newExtensionForm', handlers.submit.newExtension);
        currentPage.on('submit', '.info-box form', handlers.submit.editExtension);
        currentPage.on('click', '.extensionBox .btn-danger', handlers.click.removeExtension);
        
        loadSipAccountsSettingsBtn.click(
            function(){
                disableMenuVoices();
                pageTitle.text('Account SIP');
                $(this).addClass('active');
                
                $.get('configs/accounts.php', function(data){
                    currentPage.html('<button type="button" class="btn btn-success" onclick="$(\'#newAccountFormContainer\').toggleClass(\'hidden\')">Aggiungi un account SIP</button><br/><br/><div id="newAccountFormContainer" class="box box-solid box-default hidden"> <div class="box-header"> <h3 class="box-title">Inserisci i dati</h3> </div><div class="box-body"> <form class="form-horizontal" onsubmit="submitNewAccountForm()"> <div class="form-group"> <label for="newAccountUsername" class="col-sm-2 control-label">Username</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newAccountUsername" placeholder="Username"> </div></div><div class="form-group"> <label for="newAccountPassword" class="col-sm-2 control-label">Password</label> <div class="col-sm-10"> <input type="password" class="form-control" id="newAccountPassword" placeholder="Password"> </div></div><div class="form-group"> <label for="newAccountPhoneNumber" class="col-sm-2 control-label">Numero</label> <div class="col-sm-10"> <input type="tel" class="form-control" id="newAccountPhoneNumber" placeholder="Numero"> <span class="help-block">Inserire il numero di telefono associato a questo account</span> </div></div><div class="form-group"> <label for="newAccountDomain" class="col-sm-2 control-label">Dominio</label> <div class="col-sm-10"> <input type="text" class="form-control" id="newAccountDomain" placeholder="Dominio o indirizzo IP"> <span class="help-block">Inserire un indirizzo IP nel formato XXX.XXX.XXX.XXX (es: 192.168.1.1) o un nome di dominio</span> </div></div><div class="form-group"> <div class="col-sm-offset-2 col-sm-10"> <button type="submit" class="btn btn-default">Conferma</button> </div></div></form> </div></div>' + data);
                    $('form').submit(handlers.submit.noSubmit);
                });
                
                document.title = "Asterisk WebUI | Account SIP";
            }
        );
        
        loadAsteriskSettingsBtn.click(
            function(){
                pageTitle.text('Asterisk');
                disableMenuVoices();
                $(this).addClass('active');
                
                document.title = "Asterisk WebUI | Asterisk";
                
                currentPage.html('<div class="box box-default"> <div class="box-header with-border"> <h3 class="box-title">IP esterno del server Asterisk</h3> <div class="box-tools pull-right"> <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button> </div></div><div class="box-body"> Potresti volere utilizzare questo IP come IP esterno: </div><div class="overlay"> <i class="fa fa-refresh fa-spin"></i></div></div>');
                
                $.get('configs/asterisk.php', function(data){
                    
                    currentPage.html(data + '<form onsubmit="submitNewExternalIp()"> <form class="form-horizontal"> <div class="form-group"> <label for="externalIP" class="col-sm-1 control-label">IP esterno</label> <div class="col-sm-4"> <input type="text" class="form-control" id="externalIP" placeholder="Indirizzo IP"> <span class="help-block">Inserire un indirizzo IP nel formato XXX.XXX.XXX.XXX (es: 192.168.1.1)</span> </div></div><div class="form-group"> <div class="col-sm-1"> <button type="submit" class="btn btn-default">Conferma</button> </div></div></form>');
                
                    $('form').submit(handlers.submit.noSubmit);
                });
                
            }
        );
        
        loadExtensionsSettingsBtn.click(handlers.click.loadExtensionsSettingsBtn);
        
        saveSettingsBtn.click(handlers.click.saveUndoSettingsBtns);        
        undoSettingsBtn.click(handlers.click.saveUndoSettingsBtns);
        
        loadLogsBtn.click(handlers.click.loadLogsBtn);
        
        loadContactsBtn.click(handlers.click.loadContactsBtn);
        
        dialerBtns.click(handlers.click.dialerBtns);
        
    });
}

asteriskWebUI();