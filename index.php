<?php include __DIR__ . "/users/landing-header.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>TravelEase Camiguin - Home</title>
<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
<link rel="stylesheet" href="plugins/AdminLTE-4.0.0-rc4/dist/css/adminlte.min.css">
<link rel="icon" href="image/easeico.ico" type="image/x-icon"> 
<style>
.hero-section {
    background: url('image/background.png') no-repeat center top;
    background-size: 260px auto;
    min-height: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding-top: 220px;
    text-align: center;
}

.section-title {
    margin-top: 60px;
    margin-bottom: 30px;
    text-align: center;
}

.adventure-wrapper {
    position: relative;
    overflow: hidden;
    margin: 20px 0;
}
.adventure-container {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    scroll-behavior: smooth;
    padding-bottom: 10px;
}
.adventure-container::-webkit-scrollbar {
    display: none;
}
.adventure-card {
    min-width: 250px;
    flex: 0 0 auto;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.adventure-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}
.adventure-card .card-body {
    padding: 10px;
    text-align: center;
}

.arrow-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    border: none;
    color: #fff;
    padding: 10px 15px;
    cursor: pointer;
    z-index: 2;
    border-radius: 50%;
}
.arrow-left { left: 0; }
.arrow-right { right: 0; }

.carousel-item img {
    height: 400px;
    width: 100%;
    border-radius: 8px;
    object-fit: cover;
    object-position: center center;
}

#carouselAdventure .carousel-item img {
    height: 600px;
    width: 100%;
    border-radius: 8px;
    object-fit: cover;
    object-position: center center;
}

@media (max-width: 768px) {
    .carousel-item img,
    #carouselAdventure .carousel-item img {
        height: auto;
        object-fit: contain;
        border-radius: 0;
    }
}
</style>
</head>
<body>

<!-- Hero / Banner -->
<section class="hero-section text-center text-dark">
    <h1>Welcome To TravelEase Camiguin</h1>
    <p>Your guide to explore Camiguin's tours and rentals!</p>
    <a href="users/user-register.php" class="btn btn-primary btn-lg mt-3">Get Started</a>
</section>

<!-- Adventure Section -->
<section class="container section-title" id="adventure">
    <h2>Adventure Activities in Camiguin</h2>
    <div class="adventure-wrapper">
        <button class="arrow-btn arrow-left" onclick="scrollLeft()"><i class="fas fa-chevron-left"></i></button>
        <div class="adventure-container" id="adventureContainer">
            <div class="adventure-card">
                <img src="image/bike.jpg" alt="Bike Rentals">
                <div class="card-body">
                    <h5 class="card-title">Bike Rentals</h5>
                    <p>Ride around Camiguin on two wheels!</p>
                </div>
            </div>
            <div class="adventure-card">
                <img src="image/motor.jpg" alt="Motor Rentals">
                <div class="card-body">
                    <h5 class="card-title">Motor Rentals</h5>
                    <p>Travel freely with motor rentals!</p>
                </div>
            </div>
            <div class="adventure-card">
                <img src="image/other.jpg" alt="Other Rentals">
                <div class="card-body">
                    <h5 class="card-title">Other Rentals</h5>
                    <p>Rooms, tours, and more!</p>
                </div>
            </div>
            <div class="adventure-card">
                <img src="image/resort.jpg" alt="Resorts">
                <div class="card-body">
                    <h5 class="card-title">Resorts</h5>
                    <p>Relax in beautiful resorts!</p>
                </div>
            </div>
            <div class="adventure-card">
                <img src="image/cottage.jpg" alt="Cottages">
                <div class="card-body">
                    <h5 class="card-title">Cottages</h5>
                    <p>Comfortable cottages to stay!</p>
                </div>
            </div>
            <div class="adventure-card">
                <img src="image/tours.jpg" alt="Tours">
                <div class="card-body">
                    <h5 class="card-title">Tours</h5>
                    <p>Guided tours around the island!</p>
                </div>
            </div>
        </div>
        <button class="arrow-btn arrow-right" onclick="scrollRight()"><i class="fas fa-chevron-right"></i></button>
    </div>
</section>

<!-- Adventure Carousel -->
<section class="container section-title" id="adventureCarousel">
    <h2>More Adventures & Activities</h2>
    <div id="carouselAdventure" class="carousel slide" data-bs-ride="carousel" data-bs-wrap="true">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="image/waterfall.jpg" class="d-block w-100" alt="Waterfalls">
            </div>
            <div class="carousel-item">
                <img src="image/hot_spring.jpg" class="d-block w-100" alt="Hot Springs">
            </div>
            <div class="carousel-item">
                <img src="image/beach.jpg" class="d-block w-100" alt="Beach">
            </div>
            <div class="carousel-item">
                <img src="image/hiking.jpg" class="d-block w-100" alt="Hiking Trails">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselAdventure" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselAdventure" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>

<!-- FAQs -->
<section class="container section-title" id="faqs">
    <h2>Frequently Asked Questions</h2>
    <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                    How do I book a rental?
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    You need to register, login, and then you can view available rentals and book directly.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                    What types of rentals are available?
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    We offer bike rentals, motor rentals, rooms, and other services around Camiguin.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                    What are the must-visit tourist spots in Camiguin?
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Some must-visit spots include Katibawasan Falls, Sunken Cemetery, White Island, Mantigue Island, and Tuasan Falls.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                    When is the best time to visit Camiguin?
                </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    The best time to visit Camiguin is during the dry season from December to May to enjoy outdoor activities and island hopping.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFive">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                    Are there guided tours available?
                </button>
            </h2>
            <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Yes, you can book guided tours for popular attractions, waterfalls, and diving/snorkeling trips around Camiguin.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSix">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix">
                    What local delicacies should I try in Camiguin?
                </button>
            </h2>
            <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Try local treats like pastel (sweet filled buns), lanzones fruit (in season), seafood dishes, and native delicacies from local markets.
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Footer -->
<?php include __DIR__ . "/users/landing-footer.php"; ?>

<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/AdminLTE-4.0.0-rc4/dist/js/adminlte.min.js"></script>
<script>
const container = document.getElementById('adventureContainer');

function scrollRight() {
    const firstCard = container.firstElementChild;
    container.appendChild(firstCard);
}

function scrollLeft() {
    const lastCard = container.lastElementChild;
    container.insertBefore(lastCard, container.firstElementChild);
}
</script>

</body>
</html>