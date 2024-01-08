import '../bootstrap.js';


function subToChannel(guestId, callback) {
    const channel = Echo.channel(`fileUpload.${guestId}`);
    channel.listen('FileReadyToDownload', callback);
    console.log('subbed to guest channel');
}

function subToPrivate(guestId, callback) {
    const channelName = `user.${guestId}`;
    const channel = Echo.private(channelName);
    channel.listen('PrivateFileReadyToDownload', callback);
    console.log('subbed to private channel');
}

function disconnectFromPublic(guestId) {
    Echo.leaveChannel(`fileUpload.${guestId}`)
}

function disconnectFromPrivate(guestId) {
    Echo.leaveChannel(`user.${guestId}`)
}

export { subToChannel, subToPrivate, disconnectFromPublic, disconnectFromPrivate};
