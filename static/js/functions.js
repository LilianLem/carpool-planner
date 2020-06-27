document.addEventListener("DOMContentLoaded", (event) => {
    let dateElement = document.querySelector('.basic-form #return-date');
    let timeElement = document.querySelector('.basic-form #return-time');

    resetValue = (element) => {
        element.value = "";
    }

    document.querySelector('.basic-form label[for="return-date"] + p span').addEventListener("click", () => resetValue(dateElement), false);
    document.querySelector('.basic-form label[for="return-time"] + p span').addEventListener("click", () => resetValue(timeElement), false);
});