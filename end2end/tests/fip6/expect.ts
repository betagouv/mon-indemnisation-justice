import {expect as baseExpect, MatcherReturnType} from '@playwright/test';
import type {Locator} from '@playwright/test';

export {test} from '@playwright/test';

export const expect = baseExpect.extend({
    async toHaveCountBetween(locator: Locator, expected: { between: number, and: number }, options?: {
        timeout?: number
    }): Promise<MatcherReturnType> {
        const assertionName = "toHaveCountBetween";
        const count = await locator.count();

        const pass = count >= expected.between && count <= expected.and;

        console.dir(pass);
        const message = pass
            ? () => this.utils.matcherHint(assertionName, undefined, undefined, {isNot: this.isNot}) +
                '\n\n' +
                `Locator: ${locator}\n` +
                `Expected: ${this.utils.printExpected(expected)}\n` +
                `Received: ${this.utils.printReceived(count)}`
            : () => this.utils.matcherHint(assertionName, undefined, undefined, {isNot: this.isNot}) +
                '\n\n' +
                `Locator: ${locator}\n` +
                `Expected: ${this.utils.printExpected(expected)}\n` +
                `Received: ${this.utils.printReceived(count)}`

        return {
            message,
            pass: count > expected.between && count < expected.and,
            name: assertionName,
            expected: `>= ${expected.between} and <= ${expected.and}`,
            actual: count,
        };
    }
});

