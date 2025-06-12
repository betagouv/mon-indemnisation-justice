import ReactQuill, { Quill } from "react-quill-new";

// Patch pour la gestion des ul/ol dans Quill
import List, { ListContainer } from "quill/formats/list";

class UListContainer extends ListContainer {}
UListContainer.blotName = "ulist-container";
UListContainer.tagName = "UL";

class UListItem extends List {
  static register() {
    Quill.register(UListContainer);
  }
}

UListItem.blotName = "ulist";
UListItem.tagName = "LI";

UListContainer.allowedChildren = [UListItem];
UListItem.requiredContainer = UListContainer;

Quill.register({
  "formats/list": List,
  "formats/ulist": UListItem,
});

export { ReactQuill as QuillEditor };
