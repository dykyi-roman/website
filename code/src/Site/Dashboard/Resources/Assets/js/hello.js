const siteName = document.querySelector('meta[property="og:site_name"]')?.content || 'Website';
const host = window.location.origin;

console.log(
    `%c Welcome to ${siteName}! \n\n` +
    `%c Does this page need fixes or improvements? Contact with a team to help make Website more lovable! \n\n` +
    `%c 🌟 Contacts: ${host}/contact \n` +
    `%c 🚀 We like your curiosity! Help us improve Website by joining the team: ${host}/careers`,
    'font-size: 24px; font-weight: bold; color: #2196F3;',
    'font-size: 14px; color: #777;',
    'font-size: 14px; color: #666;',
    'font-size: 14px; color: #666;'
);
