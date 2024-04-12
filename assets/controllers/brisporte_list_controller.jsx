import { Controller } from 'stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import BrisPortes from '../react/components/BrisPortes';

export default class extends Controller {
    connect() {
      const container = this.element;
      const root = ReactDOMClient.createRoot(container);
      root.render(
        <React.StrictMode>
          <BrisPortes />
        </React.StrictMode>
      )
    }
}
