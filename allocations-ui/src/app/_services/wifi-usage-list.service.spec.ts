import { TestBed } from '@angular/core/testing';

import { WifiUsageListService } from './wifi-usage-list.service';

describe('WifiUsageListService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: WifiUsageListService = TestBed.get(WifiUsageListService);
    expect(service).toBeTruthy();
  });
});
