document.addEventListener("app:success", function (event) {
    toast("Success", {
        category: "success",
        description: event.description || "Operation completed successfully.",
    });
});

document.addEventListener("app:error", function (event) {
    toast("Error", {
        category: "error",
        description:
            event.description || "An error occurred. Please try again.",
    });
});

document.addEventListener("app:warning", function (event) {
    toast("Warning", {
        category: "warning",
        description:
            event.description || "Please check the details and try again.",
    });
});

document.addEventListener("app:info", function (event) {
    toast("Info", {
        category: "info",
        description: event.description || "Here is some information for you.",
    });
});

function toast(title, { category = "info", description }) {
    if (!["info", "success", "warning", "error"].includes(category)) {
        console.error("Invalid category for toast:", category);
        return;
    }

    document.dispatchEvent(
        new CustomEvent("basecoat:toast", {
            detail: {
                config: {
                    category: category,
                    title: title,
                    description: description || "",
                    cancel: {
                        label: "Dismiss",
                    },
                },
            },
        })
    );
}

window.toast = {
    success: (description) => toast("Success", { category: "success", description }),
    error: (description) => toast("Error", { category: "error", description }),
    warning: (description) => toast("Warning", { category: "warning", description }),
    info: (description) => toast("Info", { category: "info", description }),
};
