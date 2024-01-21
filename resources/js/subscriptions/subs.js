import '../bootstrap.js';


function subToChannel(guestId, callback) {
    const channel = Echo.channel(`fileUpload.${guestId}`);
    channel.listen('FileReadyToDownload', callback);
}

function subToPrivate(guestId, callback) {
    const channelName = `user.${guestId}`;
    const channel = Echo.private(channelName);
    channel.listen('PrivateFileReadyToDownload', callback);
}

function disconnectFromPublic(guestId) {
    Echo.leaveChannel(`fileUpload.${guestId}`)
}

function disconnectFromPrivate(guestId) {
    Echo.leaveChannel(`user.${guestId}`)
}

export { subToChannel, subToPrivate, disconnectFromPublic, disconnectFromPrivate};
