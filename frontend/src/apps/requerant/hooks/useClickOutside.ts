import { RefObject, useEffect, useLayoutEffect, useRef } from "react";

/**
 * Courtesy of @skrykate, seen at https://reactlevelup.com/posts/use-on-click-outside
 *
 *
 * @param element
 * @param handler
 * @param attached
 */
export const useOnClickOutside = <T extends HTMLElement>(
  element: RefObject<T>,
  handler: () => void, // Function to be called when a click outside the element occurs
  attached = true, // Flag to determine whether the event listener should be attached or not
): void => {
  // Create a `ref` that stores the latest `handler` function
  const latestHandler = useRef(handler);

  // Update `latestHandler.current` when the handler changes.
  // This ensures our `useEffect` below always gets the latest handler without
  // needing to pass it in its dependencies array,
  // which would cause the `useEffect` to re-run after every component render
  // in case the `handler` is created from scratch inside it

  // `useLayoutEffect` is used here to guarantee that the event handler
  // reference is updated before any subsequent renders or event handling take place.
  // Since there's a possibility that when using `useEffect`, at a certain moment,
  // the handler might become outdated. This is because `useEffect` is asynchronous:
  // its code executes after the browser finishes rendering. Consequently,
  // the handler will be updated after the browser's initial rendering.
  // In contrast, `useLayoutEffect` is synchronous, ensuring that its code executes
  // after all DOM mutations but before the browser updates the interface
  useLayoutEffect(() => {
    // Update `latestHandler` when the `handler` (its reference) changes
    latestHandler.current = handler;
  }, [handler]);
  // By using `useRef` and `useLayoutEffect`, this technique eliminates the need
  // to memoize the handler outside the hook (in the component) via `useCallback`.
  // It abstracts the optimization logic inside the hook, making it more convenient

  // Set up an effect to attach an event listener to the document
  // to track clicks outside the specified element.
  useEffect(() => {
    // If the event listener should not be attached or there is no valid element, do nothing
    if (!attached || !element.current) return;

    // Create an event listener that calls the handler function stored in the `ref`
    const handleClick = (e: Event) => {
      // If a handler function and a reference to the element are defined,
      // and the click occurred outside the element, invoke the handler function
      if (
        latestHandler &&
        element.current &&
        !element.current.contains(e.target as Node)
      ) {
        latestHandler.current();
      }
    };

    // Attach event listener for `mousedown` event to the `document`
    document.addEventListener("mousedown", handleClick);

    // Remove the event listener in the cleanup function to ensure proper
    // resource release and prevent memory leaks associated with the event
    // listener added during the component's lifecycle

    // The cleanup function is triggered when the component unmounts or
    // one of the dependencies changes
    return () => {
      document.removeEventListener("mousedown", handleClick);
    };

    // Include `element` and `attached` in the dependency array to ensure the `useEffect` runs when they change.
    // This guarantees that the event listener will be set up or removed based on changes in these values.
    // Although we use `latestHandler` inside `useEffect`, it's not necessary
    // to include it in the dependencies array. `useEffect` knows that the reference to `latestHandler`
    // doesn't change, so it won't re-run unless `element` and/or `attached` change or the component unmounts
  }, [element, attached]);
};
