import { Controller } from "@hotwired/stimulus"
export default class extends Controller {
    static targets = ["input","label"];

    connect() {

    }

    change() {
        const fileInput = this.inputTarget;
        const label = this.labelTarget;

        if (fileInput.files.length > 0) {
            const fileName = fileInput.files[0].name;
            label.textContent = fileName;
        }
    }
}