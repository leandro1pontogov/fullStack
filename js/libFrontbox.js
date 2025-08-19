/**
 * jquery.frontbox v1.0
 * Copyright (C) 2015 Afraware Technology Inc.
 * Licensed under the MIT license.
 * Created by Milad Rahimi [http://miladrahimi.com] on 5 March 2015.
 * http://afraware.com/product/jquery-plugin/frontbox
 * https://github.com/afraware/frontbox
 */
// jQuery Plugin Syntax
function FrontBox() {
    // Properties
    this.id = 1;
    this.obj = '#frontbox_1';
    this.type = "alert";
    this.cb = false;

    var me = this;
    // Close Event Handler Default
    this.handlerClose = function() {
        me.disappear();
        me.remove();
    }
    // Button Event Handler Default
    this.handlerBtn = function() {
        me.disappear();
        me.remove();
    }
    // Construct base elements of the dialog box
    this.create = function (message, title, direction) {
        // Check if body does exist or not
        var body = $('body');
        if (!body.length) {
            alert("FrontBox need the body element to perform your request.");
            return;
        }
        // Create a unique ID and object for the box (to support nested boxes)
        for (this.id = 1; $(this.obj).length; this.id++) this.obj = '#frontbox_' + this.id;
        var obj = this.obj;
        // Add the box elements to body
        body.append(
            '<div id="' + obj.substring(1) + '">' +
            '<div class="frontbox-back">' + '<div id="dialog" class="frontbox-main">' +
            '<div class="frontbox-close">x</div>' + '<div class="frontbox-title"></div>' +
            '<div class="frontbox-message"></div>' + '<div class="frontbox-prompt"></div>' +
            '<div class="frontbox-button-bar"></div>' + '</div>' + '</div>' + '</div>'
        );
        // Set Title and Message
        $(obj + ' .frontbox-title').html(title);
        $(obj + ' .frontbox-message').html(message);
        // Set direction RTL if required
        if (direction != undefined && direction == "rtl") {
            $(obj + ' .frontbox-main').css({
                'border-right-width': '5px',
                'border-left-width': '0'
            });
            $(obj + ' .frontbox-close').css({
                'left': '15px',
                'right': 'auto'
            });
            $(obj + ' .frontbox-main').css({
                'direction': 'rtl'
            });
        }
    };
    // Initialize Components
    this.initComponents = function () {
        this.initEvents();
        this.display();
    };
    // Display the box
    this.display = function () {
        //$(this.obj + ' .frontbox-back').fadeIn('fast'); //Comentado pelo Jonny
        //$(this.obj + ' .frontbox-main').fadeIn('slow'); //Comentado pelo Jonny
        $(this.obj + ' .frontbox-back').show();
        $(this.obj + ' .frontbox-main').show();
    };
    // Disappear
    this.disappear = function () {
        $(this.obj).hide();
        //$(this.obj).fadeOut("fast"); //Comentado pelo Jonny
        //$(this.obj).remove(); //By Jonny!!!
    };
    // Remove Frontbox
    this.remove = function () {
        $(this.obj).remove();
    };
    // Initialize Events
    this.initEvents = function() {
        var me = this;
        $(this.obj + ' .frontbox-close').on("click", function() { me.handlerClose($(this)); });
        $(this.obj + ' .frontbox-btn').on('click', function() { me.handlerBtn($(this)); });
    };
    // Set Callback
    this.callback = function (callback) {
        if (typeof callback != 'function') {
            return;
        }

        // Me
        var me = this;

        // Close Event Handler with Callback
        this.handlerClose = function() {
            me.disappear();
            callback("close");
            me.remove();
        }

        // Button Event Handler with Callback
        this.handlerBtn = function(btnTarget) {
            const arrArguments = [];

            // Text Prompt: Return the answer
            if (me.type == 'text') {
                var ans = $(me.obj + ' input').val();
                if (ans == '') { return; }
                arrArguments.push("ok", ans);
            }
            // Selection Prompt: Return the answer
            else if (me.type == 'selection') {
                var ans = $(me.obj + ' select').val();
                if (ans == "_null") { return; }
                arrArguments.push("ok", ans);
            }
            // Other me.types: Return the button name
            else {
                arrArguments.push(btnTarget.val().toLowerCase())
            }

            me.disappear();
            callback.apply(null, arrArguments);
            me.remove();
        }
    };
    // Message/Alert
    this.alert = function (message, title, direction) {
  
        if (message == undefined) message = "Este é um alerta!";
        if (title == undefined) title = "Informação!";
        this.create(message, title, direction);
        $(this.obj + ' .frontbox-button-bar').append('<input type="button" role="img" class="frontbox-btn" value="OK"/><i></i>');
        $(this.obj + ' .frontbox-main').addClass("frontbox-info");
        this.initComponents();
        return this;
    };
    // Message/Success
    this.success = function (message, title, direction) {
        if (message == undefined) message = "Operação realizada com sucesso!";
        if (title == undefined) title = "Sucesso!";
        this.create(message, title, direction);
        //$(this.obj + ' .frontbox-button-bar').append('<div class="frontbox-btn">OK</div>');
        $(this.obj + ' .frontbox-button-bar').append('<input type="button" role="img" class="frontbox-btn" value="OK"/>');
        //$(this.obj + ' .frontbox-main').css({'border-color': 'rgb(39,174,96)'});
        $(this.obj + ' .frontbox-main').addClass("frontbox-succes");
        this.initComponents();
        return this;
    };
    // Message/Warning
    this.warning = function (message, title, direction) {
        if (message == undefined) message = "Esta operação pode ter consequências!";
        if (title == undefined) title = "Atenção!";
        this.create(message, title, direction);
  
        var BtnDlg = "BtnDlg" + this.id;
        $(this.obj + ' .frontbox-button-bar').append('<input id="' + BtnDlg + '" role="img" type="button" class="frontbox-btn" value="OK"/>');
        setTimeout(function () { $('#' + BtnDlg).focus(); }, 500);
  
        $(this.obj + ' .frontbox-main').addClass("frontbox-warning");
        this.initComponents();
        return this;
    };
    // Message/Error
    this.error = function (message, title, direction) {
        if (message == undefined) message = "Um problema inesperado ocorreu. Aguarde alguns minutos e tente novamente. Caso o problema persista, entre em contato com o suporte do sistema.";
        if (title == undefined) title = "Erro de Sistema!";
        this.create(message, title, direction);
  
        var BtnDlg = "BtnDlg" + this.id;
        $(this.obj + ' .frontbox-button-bar').append('<input id="' + BtnDlg + '" role="img" type="button" class="frontbox-btn" value="OK"/>');
        //setTimeout(function(){$('#'+BtnDlg).focus();},500);
  
        var BtnDlgSuporte = "BtnSuporte" + this.id;
        $(this.obj + ' .frontbox-button-bar').append('<input id="' + BtnDlgSuporte + '" role="img" type="button" class="frontbox-btn" value="Suporte"/>');
        $(this.obj + ' #' + BtnDlgSuporte).on("click", function () {
            $("#BtnChat").click();
        });
  
        $(this.obj + ' .frontbox-main').addClass("frontbox-error");
        this.initComponents();
        return this;
    };
    // Question/Yes_No
    this.yes_no = function (message, title, direction) {
        if (message == undefined) message = "Deseja realmente realizar esta operação?";
        if (title == undefined) title = "Confirmação";
        this.create(message, title, direction);
  
        var BtnSim = "BtnSim" + this.id;
        var BtnNao = "BtnNao" + this.id;
  
        //$(this.obj + ' .frontbox-button-bar').append('<input name="'+BtnSim+'" id="'+BtnSim+'" class="frontbox-btn" type="button" value="Sim"/>').append('<input id="'+BtnNao+'" type="button" class="frontbox-btn" value="Não"/>');
        $(this.obj + ' .frontbox-button-bar').append('<input type="button" role="img" id="' + BtnSim + '" tabindex="1001" class="frontbox-btn" value="Sim"/>');
        $(this.obj + ' .frontbox-button-bar').append('<input type="button" role="img" id="' + BtnNao + '" tabindex="1002" class="frontbox-btn" value="Não"/>');
        $(this.obj + ' .frontbox-main').addClass("frontbox-info");
        this.initComponents();
  
        var objBtn = $('#' + BtnSim);
  
        setTimeout(function () { objBtn.focus(); }, 100);
        setTimeout(function () { objBtn.focus(); }, 200);
        setTimeout(function () { objBtn.focus(); }, 300);
        setTimeout(function () { objBtn.focus(); }, 500);
        setTimeout(function () { objBtn.focus(); }, 1000);
  
        return this;
    };
    // Question/OK_Cancel
    this.ok_cancel = function (message, title, direction) {
        if (message == undefined) message = "The operation will getting started.";
        if (title == undefined) title = "Confirmação";
        this.create(message, title, direction);
        $(this.obj + ' .frontbox-button-bar').append('<div class="frontbox-btn">OK</div>').append(
            '<div class="frontbox-btn">Cancel</div>');
        this.initComponents();
        return this;
    };
    // Question/Retry_Ignore_Abort
    this.retry_ignore_abort = function (message, title, direction) {
        if (message == undefined) message = "There is a problem in performing the operation.";
        if (title == undefined) title = "Confirmação";
        this.create(message, title, direction);
        $(this.obj + ' .frontbox-button-bar').append('<div class="frontbox-btn">Retry</div>').append(
            '<div class="frontbox-btn">Ignore</div>').append('<div class="frontbox-btn">Abort</div>');
        this.initComponents();
        return this;
    };
    // Prompt/Text
    this.text = function (message, title, placeholder, direction) {
        if (message == undefined) message = "Enter your text:";
        if (title == undefined) title = "Message";
        if (placeholder == undefined) placeholder = "Enter...";
        this.create(message, title, direction);
        $(this.obj + ' .frontbox-button-bar').append('<div class="frontbox-btn">OK</div>');
        $(this.obj + ' .frontbox-prompt').show().append('<input type="text" placeholder="' + placeholder + '">');
        this.type = "text";
        this.initComponents();
        return this;
    };
    // Prompt/Selection
    this.selection = function (message, title, options, placeholder, direction) {
        if (message == undefined) message = "Select one of these options:";
        if (title == undefined) title = "Message";
        if (placeholder == undefined) placeholder = "Select...";
        if (options == undefined || !$.isArray(options)) options = [];
        this.create(message, title, direction);
        $(this.obj + ' .frontbox-button-bar').append('<div class="frontbox-btn">OK</div>');
        $(this.obj + ' .frontbox-prompt').append(
            '<select title="' + title + '"><option value="_null">' + placeholder +
            '</option></select>').show();
        var id = this.obj;
        $.each(options, function (key, value) {
            value = $.trim(value);
            if (value != '') $(id + ' .frontbox-prompt select').append('<option>' + value + '</option>');
        });
        this.type = "selection";
        this.initComponents();
        return this;
    };
    // The End!
}