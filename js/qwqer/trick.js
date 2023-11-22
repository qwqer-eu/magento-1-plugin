Ajax.Responders.register({
    onComplete: function() {
    	qwqer.init();
        qwqerdoor.init();
        qwqerparcel.init();
    }
});
window.onload = function(){
    qwqer.init();
    qwqerdoor.init();
    qwqerparcel.init();
}