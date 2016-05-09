/**
 * Created by admin on 2015/5/22.
 */
(function(global){
    var nativeObj,
        Turingcat = function(){
            nativeObj = global.turingcat||{};
            return {
                closeWindow:instanceAPI.closeWindow
            }
        },
        log_error = function(msg){console.error(msg)};
    var instanceAPI = {
        closeWindow:function(){
            if (nativeObj.closeWindow){
                nativeObj.closeWindow();
            }else{
                log_error("closeWindow doesn't exist!");
            }
        },
        noConflict:function(){
        }
    };
    global.Turingcat = Turingcat;
})(this);
