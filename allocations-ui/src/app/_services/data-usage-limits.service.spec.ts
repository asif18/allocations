import { TestBed } from '@angular/core/testing';

import { DataUsageLimitsService } from './data-usage-limits.service';

describe('DataUsageLimitsService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: DataUsageLimitsService = TestBed.get(DataUsageLimitsService);
    expect(service).toBeTruthy();
  });
});
