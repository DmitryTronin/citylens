// Random JavaScript snippets
const greetings = ['Hello', 'Hi', 'Hey', 'Howdy'];
const randomGreeting = () => greetings[Math.floor(Math.random() * greetings.length)];

console.log(`${randomGreeting()}, world!`);