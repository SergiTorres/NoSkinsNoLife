// just do it inline.
username = 'seyroku'; // Bot's Steam Username
password = 'S45481408t'; // Bot's Steam Password
 
// Define all our included variables
var steam      = require('steam');
var steamtrade = require('steam-trade');
var winston    = require('winston');
var readline   = require('readline');
var fs         = require('fs');
 
// We have to use application IDs in our requests, so this is just a helper
var appid = {
    TF2: 440,
    Steam: 753
};
// We also have to know context IDs which are a bit tricker.
// For Steam, ID 1 is gifts, and 6 is trading cards, emoticons, backgrounds
// For TF2 and DOTA we always use 2.  These are just some default values.
var contextid = {
    TF2: 2,
    Steam: 6
}
 
// We'll reference this to make sure we're only in one trade at a time.
var inTrade = false;
 
// Since we're taking user input inside the trade window, we have to make our
// inventory global.  Otherwise our trade chat listener doesn't know what we have.
var myBackpack;
 
// Setup readline to read from console.  This is used for Steam Guard codes.
var rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});
 
// Setup logging to file and console
var logger = new (winston.Logger)({
        transports: [
            new (winston.transports.Console)({
                colorize: true, 
                level: 'debug'
            }),
            new (winston.transports.File)({
                level: 'info', 
                timestamp: true, 
                filename: 'cratedump.log', 
                json: false
            })
        ]
});
 
// Initialize the Steam client and our trading library
var client = new steam.SteamClient();
var trade  = new steamtrade();
 
// Our library has a list of Steam servers in case one isn't provided.
// You can define them if you want to make sure they're accurate;
// Just make sure to keep them up to date or you could run into issues.
if(fs.existsSync('servers.json')) {
    steam.servers = JSON.parse(fs.readFileSync('servers.json'));
}
 
// We can provide a sentry file for Steam Guard when we login to avoid
// having to enter a code each time.  If we have one saved to file, use it.
var sentryfile;
if(fs.existsSync('sentryfile.' + username + '.hash')) {
    sentryfile = fs.readFileSync('sentryfile.' + username + '.hash');
}
 
// Now we can finally start doing stuff!  Let's try logging in.
client.logOn({
    accountName: username, 
    password: password, 
    shaSentryfile: sentryfile // If null, a new Steam Guard code will be requested
});
 
// If Steam returns an error the "error" event is emitted.
// We can deal with some of them.
// See docs on Event Emitter to understand how this works: 
// http://nodejs.org/api/events.html
client.on('error', function(e) {
    // Error code for invalid Steam Guard code
    if (e.eresult == steam.EResult.AccountLogonDenied) {
        // Prompt the user for Steam Gaurd code
        rl.question('Steam Guard Code: ', function(code) {
            // Try logging on again
            client.logOn({
                accountName: username,
                password: password,
                authCode: code
            });
        });
    } else { // For simplicity, we'll just log anything else.
        // A list of ENUMs can be found here: 
        // https://github.com/SteamRE/SteamKit/blob/d0114b0cc8779dff915c4d62e0952cbe32202289/Resources/SteamLanguage/eresult.steamd
        logger.error('Steam Error: ' + e.eresult);
        // Note: Sometimes Steam returns InvalidPassword (5) for valid passwords.
        // Simply trying again solves the problem a lot of the time.
    }
});
 
// If we just entered a Steam Guard code, the "sentry" event goes off
// with our new hash.
client.on('sentry', function(sentry) {
    logger.info('Got new sentry file hash from Steam.  Saving.');
    fs.writeFile('sentryfile.' + username + '.hash', sentry);
});
 
// After successful login...
client.on('loggedOn', function() {
    logger.info('Logged on to Steam');
    // Optional: Rename the bot on login.
    client.setPersonaName("CrateDumpBot"); 
    // Make sure we're not displaying as online until we're ready
    client.setPersonaState(steam.EPersonaState.Offline); 
});
 
/* At this point, you should be logged into Steam but appear offline.
 * We haven't logged into the web API yet to do any trading.
 * Steam hands us a session ID before we can use the API.
 * Additionally, our Trade library requires the session ID and cookie,
 * so we have to wait for the following event to be emitted.
*/
client.on('webSessionID', function(sessionid) {
    trade.sessionID = sessionid; // Share the session between libraries
    client.webLogOn(function(cookie) {
        cookie.forEach(function(part) { // Share the cookie between libraries
            trade.setCookie(part.trim()); // Now we can trade!
        });
        logger.info('Logged into web');
        // No longer appear offline
        client.setPersonaState(steam.EPersonaState.LookingToTrade); 
    });
});









// If a user messages me through Steam...
client.on('friendMsg', function(steamID, message, type) {
    //client.sendMessage("76561197984930302", 'putaaaaaaaaa&apos'); // adryy
        //client.sendMessage("76561198098716712", 'putaaaaaaaaa&apos'); //metal
        //client.sendMessage("76561197989513578", 'Spameada por ignorarme, perro!&apos');

    if (type == steam.EChatEntryType.ChatMsg) { // Regular chat message
        logger.info('[' + steamID + '] MSG: ' + message); 

// Log it

        client.sendMessage("76561198098716712", 'Soy el puto bot&apos');
    }
});