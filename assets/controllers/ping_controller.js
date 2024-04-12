import { Controller } from 'stimulus';

import ReactDOM from 'react-dom';
import React from 'react';
import Ping from '../react/components/Ping';

export default class extends Controller {
    connect() {
      ReactDOM.render(
          <Ping />,
          this.element
      )
    }
}
