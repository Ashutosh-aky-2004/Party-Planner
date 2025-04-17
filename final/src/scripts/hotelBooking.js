let hotelId = localStorage.getItem('hotelIdUserSelected');
let selectedItems = [];

function onloadPage() {
    loadHotelDetails();
    chooseFood();
    moreItems();
    attachEventListeners(); // Attach event listeners
}

// Load hotel details
function loadHotelDetails() {
    let hotelData = hotelArray[hotelId - 1];
    let ratingCountString = "⭐".repeat(hotelData.rating.score);
    let ammentiesElementData = hotelData.amenities.map(element =>
        `<span class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-yellow-900 dark:text-yellow-300">${element}</span>`
    ).join('');

    let cardDiv = document.getElementById('hotelDetailCardOnBookPage');
    cardDiv.innerHTML = `
        <div class="w-full max-w-4xl mx-auto bg-black shadow-lg rounded-xl overflow-hidden border border-gray-800">
    <div class="md:flex">
        <div class="md:w-1/2 overflow-hidden">
            <img class="w-full h-64 object-cover md:h-full filter grayscale-50 
                  transform origin-top-left transition-all duration-300 
                  hover:scale-[1.05] hover:-translate-x-1 hover:-translate-y-1" 
                 src="${hotelData.imagePath}" alt="Hotel Image">
        </div>
        <div class="p-6 md:w-1/2 text-white">
            <h2 class="text-3xl font-bold">${hotelData.name}</h2>
            <p class="text-gray-300 mt-1">${hotelData.location.address}, ${hotelData.location.city}, ${hotelData.location.country}</p>
            <p class="text-lg font-semibold mt-3">Rating: <span class="text-gray-400">${ratingCountString}</span></p>
            <p class="text-lg font-semibold mt-2">Amenities:</p>
            <div class="flex flex-wrap gap-2 mt-2">
                ${ammentiesElementData}
            </div>
            <p class="text-lg font-semibold mt-2">Capacity: <span class="text-gray-300">${hotelData.capacity}</span></p>
            <p class="text-xl font-bold text-white mt-2">Cost: ₹${hotelData.price.amountPerNight}</p>
        </div>
    </div>
</div>
`;

    // Add hotel cost to total section
    updateTotalAmountSection(selectedItems, getNoOfPerson(), hotelData.price.amountPerNight);
}

// Load food items
function chooseFood() {
    let itemsContainerElement = document.querySelector('#chooseFood');
    if (!itemsContainerElement) return;

    let innerHtml = foodOfferedArray.map(item => `
        <div class="h-72 w-72 bg-black shadow-md rounded-lg overflow-hidden transform transition duration-300 hover:scale-105 border border-gray-800">
    <div class="relative h-48">
        <img class="w-full h-full object-cover transition-transform duration-300 hover:scale-110 filter grayscale-50" src="${item.images}" alt="Food Image">
        <div class="absolute inset-0 bg-black bg-opacity-70 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
            <h3 class="text-white text-xl font-bold">${item.name}</h3>
        </div>
    </div>
    <div class="p-4 flex justify-between items-center">
        <h4 class="text-gray-300 font-semibold text-md">₹${item.costPerHead} Per Head</h4>
        <button class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-100 hover:text-black transition duration-200 " onclick="addItem(${item.id})">Add</button>
    </div>
</div>
`).join('');

    itemsContainerElement.innerHTML = innerHtml;
}

// Load additional items
function moreItems() {
    let itemsContainerElement = document.querySelector('#chooseMoreItems');
    if (!itemsContainerElement) return;

    let innerHtml = moreItemsArray.map(item => `
       <div class="h-72 w-72 bg-black shadow-md rounded-lg overflow-hidden transform transition duration-300 hover:scale-105">
    <div class="relative h-48">
        <img class="w-full h-full object-cover transition-transform duration-300 hover:scale-110" src="${item.images}" alt="Food Image">
        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
            <h3 class="text-white text-xl font-bold">${item.name}</h3>
        </div>
    </div>
    <div class="p-4 flex justify-between items-center">
        <h4 class="text-white font-semibold text-md">₹${item.costPerHead} Per Head</h4>
        <button class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-100 hover:text-black transition duration-200 " onclick="addItem(${item.id})">Add</button>
    </div>
</div>
`).join('');

    itemsContainerElement.innerHTML = innerHtml;
}

// Get number of persons
function getNoOfPerson() {
    return parseInt(document.getElementById('noofperson').value) || 1;
}

// Add item function
function addItem(itemId) {
    let item = [...foodOfferedArray, ...moreItemsArray].find(i => i.id === itemId);

    if (item) {
        // Check if the item is already in the selectedItems array
        let alreadyAdded = selectedItems.some(selectedItem => selectedItem.id === itemId);

        if (!alreadyAdded) {
            selectedItems.push(item);
            updateTotalAmountSection(selectedItems, getNoOfPerson());
        } else {
            alert(`${item.name} is already added!`);
        }
    }
}


// Update total amount section
function getHotelPrice() {
    let hotelId = localStorage.getItem('hotelIdUserSelected');
    if (!hotelId) return 0;  // If no hotel selected, return 0

    let hotelData = hotelArray.find(hotel => hotel.id == hotelId);
    return hotelData ? hotelData.price.amountPerNight : 0;
}

function updateTotalAmountSection(selectedItems, noOfPerson) {
    let totalAmount = 0;
    let totalAmountSection = document.getElementById('totalAmountSection');
    totalAmountSection.innerHTML = ""; // Clear previous content

    // Fetch Hotel Price
    let hotelCost = getHotelPrice();
    totalAmount += hotelCost;

    // Add Hotel Cost to Total Amount Section
    totalAmountSection.innerHTML += `
        <div class="flex justify-between pr-4 pl-4 text-lg">
            <div class="font-lightbold">Hotel</div>
            <div class="font-lightbold">₹${hotelCost}</div>
        </div>
    `;

    // Add Selected Items
    selectedItems.forEach(item => {
        let itemCost = (item.name !== "DJ") ? item.costPerHead * noOfPerson : item.costPerHead;
        totalAmount += itemCost;

        totalAmountSection.innerHTML += `
            <div class="flex justify-between pr-4 pl-4 text-lg">
                <div class="font-lightbold">${item.name}</div>
                <div class="font-lightbold">₹${itemCost}</div>
            </div>
        `;
    });

    // Update Total Price
    document.getElementById('total').innerHTML = `₹${totalAmount}`;
}


// Attach event listener to noOfPerson input
function attachEventListeners() {
    let noOfPersonInput = document.getElementById('noofperson');
    if (noOfPersonInput) {
        noOfPersonInput.addEventListener('input', function () {
            updateTotalAmountSection(selectedItems, getNoOfPerson());
        });
    }
}

// Form submission
document.getElementById('eventForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const fullName = document.getElementById('full_name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const noOfPerson = document.getElementById('noofperson').value;
    const eventType = document.getElementById('countries').value;

    const formData = { fullName, email, phone, noOfPerson, eventType };
    localStorage.setItem('eventDetails', JSON.stringify(formData));
});

onloadPage();
