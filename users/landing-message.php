<?php
include("landing-header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>TravelEase Camiguin - Chatbot</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<style>
  body { background: #f8f9fa; display: flex; flex-direction: column; min-height: 100vh; }
  .chat-container { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 15px; }
  .chat-window { width: 100%; max-width: 700px; height: 600px; background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); display: flex; flex-direction: column; overflow: hidden; }
  .chat-header { background: #0d6efd; color: #fff; padding: 15px; font-weight: bold; font-size: 18px; display: flex; align-items: center; gap: 10px; }
  .chat-body { flex: 1; padding: 15px; overflow-y: auto; background: #f8f9fa; }
  .chat-message { margin-bottom: 12px; display: flex; align-items: flex-end; }
  .chat-message.user .bubble { background: #0d6efd; color: #fff; margin-left: auto; }
  .chat-message.bot .bubble { background: #e9ecef; color: #000; margin-right: auto; }
  .bubble { padding: 12px 16px; border-radius: 20px; max-width: 75%; font-size: 16px; }
  .chat-footer { display: flex; border-top: 1px solid #ddd; }
  .chat-footer input { border: none; flex: 1; padding: 15px; font-size: 16px; outline: none; }
  .chat-footer button { background: #0d6efd; border: none; color: #fff; padding: 0 25px; font-size: 18px; }
</style>
</head>
<body>

<div class="chat-container">
  <div class="chat-window">
    <div class="chat-header">
      <i class="fas fa-robot"></i> TravelEase Camiguin Assistant
    </div>
    <div class="chat-body" id="chatBody">
      <div class="chat-message bot">
        <div class="bubble">Hello 👋! I'm your TravelEase Camiguin AI assistant. I specialize in providing detailed information about Camiguin's amazing tourist spots, attractions, and travel tips. Ask me about any destination in Camiguin, and I'll give you all the information you need! For rentals and bookings, please check our website's Rentals section for real-time availability. 🏝️</div>
      </div>
    </div>
    <div class="chat-footer">
      <input type="text" id="chatInput" placeholder="Type your message...">
      <button id="sendBtn"><i class="fas fa-paper-plane"></i></button>
    </div>
  </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
const chatBody = document.getElementById('chatBody');
const chatInput = document.getElementById('chatInput');
const sendBtn = document.getElementById('sendBtn');

function addMessage(message, sender = 'bot') {
  const msgDiv = document.createElement('div');
  msgDiv.classList.add('chat-message', sender);
  const bubble = document.createElement('div');
  bubble.classList.add('bubble');
  bubble.innerHTML = message;
  msgDiv.appendChild(bubble);
  chatBody.appendChild(msgDiv);
  chatBody.scrollTop = chatBody.scrollHeight;
}

function botReply(userText) {
  let response = "I'm not sure I understand. Try asking about Camiguin’s popular spots like White Island, Katibawasan Falls, Ardent Hot Spring, Mantigue Island, or Mount Hibok-Hibok — or check the 'Explore More' section for more info!";
  const text = userText.trim().toLowerCase();

  // ✅ Greetings
  if (text.match(/\b(hi|hello|hey|good morning|good afternoon)\b/)) {
    response = "Hi there! 👋 Welcome to TravelEase Camiguin — your friendly island guide! Feel free to ask me about any tourist destination in Camiguin 🌴";
  }

  // ✅ About the system
  else if (text.includes("owner") || text.includes("who made") || text.includes("created") || text.includes("purpose") || text.includes("about you")) {
    response = "TravelEase Camiguin was created by BSIT students from CPSC to help travelers explore and experience the best of Camiguin Island. 🌺";
  }

  else if (text.includes("website") || text.includes("system") || text.includes("purpose")) {
    response = "🌐 TravelEase Camiguin is a user-friendly website designed to help tourists explore the island’s breathtaking attractions and hidden gems. The purpose is to provide reliable travel info, assist with rentals, and make planning easier and more enjoyable! ✨";
  }

  else if (text.includes("about camiguin") || text.includes("tell me about camiguin") || text.includes("camiguin island")) {
    response = "Camiguin, the 'Island Born of Fire', is a paradise filled with nature, history, and adventure! From white beaches to hot springs and mountains — this island is perfect for every kind of traveler. 🌋";
  }
  // 🌴 Tourist Spot Responses
  else if (text.includes("white island")) {
    response = "🏝️ White Island — a pure white sandbar off Mambajao with panoramic views of Mt. Hibok-Hibok and Mt. Vulcan. Entrance Tip: Bring sunscreen and water; there’s no shade!";
  }
  else if (text.includes("mantigue")) {
    response = "🐠 Mantigue Island — a marine sanctuary off Mahinog with white sand and clear waters. Great for snorkeling and diving! Boats are available from San Roque.";
  }
  else if (text.includes("sunken cemetery")) {
    response = "⚓ Sunken Cemetery — historical underwater site in Catarman, marked by a giant cross. Visit at sunset for a beautiful view.";
  }
  else if (text.includes("katibawasan")) {
    response = "💦 Katibawasan Falls — a 70-meter waterfall in Mambajao surrounded by forest. Entrance Tip: Bring swimwear and enjoy the cool pool!";
  }
  else if (text.includes("tuasan")) {
    response = "🌿 Tuasan Falls — located in Catarman, perfect for nature lovers. Less crowded, clean, and peaceful.";
  }
  else if (text.includes("ardent") || text.includes("hot spring")) {
    response = "♨️ Ardent Hot Spring — natural hot spring at the foot of Mt. Hibok-Hibok. Best visited at night for a relaxing soak.";
  }
  else if (text.includes("sto nino") || text.includes("santo niño") || text.includes("cold spring")) {
    response = "❄️ Sto. Niño Cold Spring — cool and refreshing natural pool in Catarman. Ideal for family picnics!";
  }
  else if (text.includes("old church") || text.includes("gui-ob")) {
    response = "⛪ Old Church Ruins (Gui-ob Church) — remnants of a Spanish-era church destroyed by a volcanic eruption in 1871.";
  }
  else if (text.includes("hibok") || text.includes("hibok-hibok")) {
    response = "🏔️ Mt. Hibok-Hibok — an active volcano great for hiking, with scenic views over Camiguin and Bohol Sea.";
  }
  else if (text.includes("binangawan")) {
    response = "🌊 Binangawan Falls — hidden twin waterfalls in Sagay surrounded by lush forest. A hidden gem for adventurers!";
  }
  else if (text.includes("soda pool")) {
    response = "🥤 Soda Water Swimming Pool — a unique natural spring in Catarman with carbonated water! Great for a refreshing dip.";
  }
  else if (text.includes("burias shoal")) {
    response = "🐚 Burias Shoal — a diver’s paradise with colorful coral reefs and marine life near Mahinog.";
  }
  else if (text.includes("mangrove") || text.includes("katunggan")) {
    response = "🌱 Katunggan Mangrove Park — a peaceful mangrove forest walkway in Mahinog, great for eco-tours.";
  }
  else if (text.includes("kibila") || text.includes("clam")) {
    response = "🐚 Kibila White Beach & Giant Clam Sanctuary — a marine sanctuary in Guinsiliban where you can see giant clams up close!";
  }
  else if (text.includes("chang view")) {
    response = "🌅 Chang View Deck — a viewpoint offering stunning panoramas of Camiguin’s mountains and coastline.";
  }
  else if (text.includes("momot")) {
    response = "🏖️ Momot Beach — a quiet pebble beach in Sagay, perfect for peaceful relaxation away from crowds.";
  }
  else if (text.includes("taguines lagoon")) {
    response = "🚤 Taguines Lagoon — a calm man-made lagoon in Mahinog where you can enjoy boating and sightseeing.";
  }
  else if (text.includes("dinangasan")) {
    response = "🌄 Dinangasan Tourism View Deck — a scenic hilltop view in Catarman offering breathtaking island views.";
  }
  else if (text.includes("mount vulcan") || text.includes("vulcan")) {
    response = "🌋 Mount Vulcan — also called the ‘Old Volcano’; hike the Stations of the Cross trail with ocean views.";
  }
  else if (text.includes("walkway")) {
    response = "🚶 Camiguin Walkway — a pilgrimage site on Mount Vulcan with scenic views and the Stations of the Cross.";
  }
  else if (text.includes("balete") || text.includes("centennial tree")) {
    response = "🌳 Centennial Balete Tree — a centuries-old tree surrounded by small pools; a great stop for nature lovers.";
  }
  else if (text.includes("guisi falls")) {
    response = "💧 Guisi Falls — a lesser-known but beautiful waterfall hidden in the forest of Camiguin.";
  }
  else if (text.includes("tuasan river")) {
    response = "🏞️ Tuasan River Park — a riverside relaxation spot near Tuasan Falls, perfect for picnics.";
  }
  else if (text.includes("zipline")) {
    response = "⚡ Camiguin Zipline Adventure — located in Mahinog; offers an exciting aerial view of the island.";
  }
  else if (text.includes("museum") || text.includes("heritage")) {
    response = "🏛️ Camiguin Heritage Site & Museum — displays artifacts and stories about Camiguin’s rich history.";
  }
  else if (text.includes("souvenir") || text.includes("pasalubong")) {
    response = "🛍️ Camiguin Souvenir & Pasalubong Center — grab local delicacies and handmade crafts!";
  }
  else if (text.includes("bonbon church")) {
    response = "⛪ Bonbon Church Ruins — ancient ruins from Spanish times; a quiet historical site in Camiguin.";
  }
  else if (text.includes("walk of peace")) {
    response = "🕊️ Camiguin Walk of Peace — a serene garden path in San Miguel symbolizing unity and tranquility.";
  }
  
  // Rentals or Booking
  else if (text.includes("rental") || text.includes("book") || text.includes("accommodation")) {
    response = "🏡 You can explore rentals and accommodations in our Rentals section — real-time listings with details and contact info.";
  }


  // 🏝️ Tourist Spots
  else if (
    text.includes("tourist spot") || 
    text.includes("spots in camiguin") || 
    text.includes("places in camiguin") ||
    text.includes("what to visit") || 
    text.includes("camiguin tourist") || 
    text.includes("where to go")
  ) {
    response = `
🌋 <b>Complete List of Tourist Spots in Camiguin Island</b><br>
<i>"The Island Born of Fire" — where nature, history, and adventure meet!</i> 🌴  

<hr>

🏖️ <b>Beaches and Islands</b><br>
White Island Sandbar — A stunning white sandbar near Mambajao with panoramic views of Mount Hibok-Hibok and the sea.<br>
Mantigue Island Nature Park — A tropical paradise perfect for snorkeling and swimming.<br>


<hr>

💦 <b>Waterfalls</b><br>
Katibawasan Falls — A 75-meter waterfall cascading beautifully amidst lush greenery. 🌿<br>
Tuasan Falls — Less crowded and great for a refreshing dip. 💧<br>
Binangawan Falls — A hidden twin waterfall in Sagay’s rainforest. 🌺  

<hr>

💧 <b>Springs and Pools</b><br>
Ardent Hot Spring — A natural hot spring warmed by volcanic energy. ♨️<br>
Santo Niño Cold Spring — A refreshing spring perfect for families. ❄️<br>
Bura Soda Pool — The only soda-water spring in the Philippines! 🫧<br>
Macao Cold Spring — A quiet natural spring surrounded by trees. 🌳  

<hr>

🌋 <b>Volcanoes and Mountains</b><br>
Mount Hibok-Hibok — A famous hiking volcano offering panoramic island views. 🏔️<br>
Mount Vulcan — Known for the Stations of the Cross trail. ✝️<br>
Old Volcano Walkway — A spiritual and scenic pilgrimage path. 🙏  

<hr>

🏛️ <b>Historical and Heritage Sites</b><br>
Sunken Cemetery — A historical underwater landmark from the 1871 eruption. ⚓<br>
Old Church Ruins (Gui-ob Church) — Remains of a Spanish-era church buried by volcanic ash. ⛪<br>
Moro Watch Tower — Built to guard against raiders, now a scenic landmark. 🕍  

<hr>

🌿 <b>Natural Parks and Sanctuaries</b><br>
Taguines Lagoon — A serene lagoon perfect for kayaking and family picnics. 🚣‍♂️<br>
Giant Clam Sanctuary — Discover colorful corals and giant clams up close! 🐚<br>
Mt. Timpoong–Hibok-Hibok Natural Monument — A UNESCO site rich in biodiversity. 🌱  

<hr>

🎉 <b>Festivals & Local Attractions</b><br>
Camiguin Lanzones Festival — Celebrating the island’s sweetest fruit. 🎭<br>
Benoni Port — The welcoming gateway to the island. 🚢<br>
Camiguin Aviation — Take a thrilling sky tour of the island! ✈️<br><br>

✨ Whether you’re seeking adventure, peace, or culture — Camiguin has something special waiting for you! 🌺
`;
  }

  // 🏠 Rentals
  else if (text.includes("rental") || text.includes("accommodation") || text.includes("rent") || text.includes("house")) {
    response = "🏡 You can explore available rentals and accommodations by visiting the Rentals section of TravelEase Camiguin. Each listing provides full details and booking options to make your trip smooth and easy! 🌴";
  }

  // 🙏 Thank you
  else if (text.includes("thank you") || text.includes("thanks")) {
    response = "You're very welcome! 😊 Enjoy your stay in Camiguin and may your island journey be full of joy and adventure! 🌺";
  }

  addMessage(response, 'bot');
}

sendBtn.addEventListener('click', () => {
  const userText = chatInput.value.trim();
  if(userText === '') return;
  addMessage(userText, 'user');
  chatInput.value = '';
  setTimeout(() => botReply(userText), 800);
});

chatInput.addEventListener('keypress', (e) => {
  if(e.key === 'Enter') sendBtn.click();
});
</script>

</body>
</html>
